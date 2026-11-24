<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * 練習課題2：会員登録・ログイン・ログアウトの Feature Test
 * （Breeze が最初から入れた tests/Feature/Auth/ のテストに足りない部分を追加）
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function registerData(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'テスト太郎',
            'email'                 => 'taro@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    // ---------- 会員登録 ----------

    // 正常系：フォーム送信 → DB に保存 → ダッシュボードへリダイレクト
    public function test_registration_saves_user_and_redirects_to_dashboard(): void
    {
        Mail::fake();

        $response = $this->post('/register', $this->registerData());

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'taro@example.com', 'name' => 'テスト太郎']);
    }

    // 正常系：登録したらウェルカムメール（Week 12）が本人あてに送られる
    public function test_registration_sends_welcome_mail(): void
    {
        Mail::fake();

        $this->post('/register', $this->registerData());

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->hasTo('taro@example.com'));
    }

    // 異常系：すでに登録されているメールアドレスは使えない
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taro@example.com']);

        $response = $this->post('/register', $this->registerData());

        $response->assertSessionHasErrors('email');
    }

    // 異常系：メールアドレスの形式が正しくない（@ がない）
    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->post('/register', $this->registerData(['email' => 'taro.example.com']));

        $response->assertSessionHasErrors('email');
    }

    // 異常系：確認用パスワードが一致しない
    public function test_registration_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', $this->registerData(['password_confirmation' => 'different123']));

        $response->assertSessionHasErrors('password');
    }

    // 境界値：パスワード7文字（8文字の1つ手前）→ NG
    public function test_registration_fails_with_7_character_password(): void
    {
        $response = $this->post('/register', $this->registerData([
            'password'              => '1234567',
            'password_confirmation' => '1234567',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    // 境界値：パスワード8文字（ちょうど）→ OK
    public function test_registration_succeeds_with_8_character_password(): void
    {
        Mail::fake();

        $response = $this->post('/register', $this->registerData([
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    // ---------- ログイン・ログアウト ----------

    // 正常系：正しいメールアドレスとパスワードでログインできる
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(); // UserFactory のパスワードは 'password'

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    // 異常系：パスワードが違うとログインできず、エラーが返る
    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // 異常系：登録されていないメールアドレスではログインできない
    public function test_user_cannot_login_with_unknown_email(): void
    {
        $response = $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // 正常系：ログアウトするとトップページに戻り、未ログインの状態になる
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
