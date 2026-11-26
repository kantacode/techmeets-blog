<?php

namespace Tests\Unit;

use App\Models\Post;
use Tests\TestCase;

/**
 * Week 17：画像の URL を CloudFront 経由にする
 * AWS_URL（filesystems.disks.s3.url）に CloudFront のドメインがあれば、その URL を返す
 */
class PostImageUrlTest extends TestCase
{
    public function test_image_url_uses_cloudfront_when_aws_url_is_set(): void
    {
        config(['filesystems.disks.s3.url' => 'https://d1234example.cloudfront.net']);
        $post = new Post(['image_path' => 'images/sample.webp']);

        $this->assertSame('https://d1234example.cloudfront.net/images/sample.webp', $post->image_url);
    }

    public function test_image_url_is_null_without_image(): void
    {
        $post = new Post(['image_path' => null]);

        $this->assertNull($post->image_url);
    }
}
