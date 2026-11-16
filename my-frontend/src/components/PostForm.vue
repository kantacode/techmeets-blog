<template>
  <form class="form" @submit.prevent="createPost">
    <h2>新規投稿</h2>

    <label>
      タイトル
      <input v-model="form.title" maxlength="200" required />
    </label>
    <p v-if="errors.title" class="error">{{ errors.title[0] }}</p>

    <label>
      カテゴリー
      <input v-model="form.category" maxlength="50" required />
    </label>
    <p v-if="errors.category" class="error">{{ errors.category[0] }}</p>

    <label>
      本文
      <textarea v-model="form.content" rows="4" required></textarea>
    </label>
    <p v-if="errors.content" class="error">{{ errors.content[0] }}</p>

    <button type="submit" :disabled="sending">
      {{ sending ? '送信中...' : '投稿する' }}
    </button>
    <p v-if="message" class="success">{{ message }}</p>
  </form>
</template>

<script setup>
// 新規投稿フォームを担当する：入力値を state で持ち、POST で Laravel に送る
import { ref } from 'vue'
import api from '../api'

const emit = defineEmits(['created'])

const emptyForm = () => ({ title: '', category: '', content: '' })
const form = ref(emptyForm())   // 入力値（v-model で入力欄とつながる）
const errors = ref({})          // Laravel から返ってきた入力エラー（422）
const sending = ref(false)      // 二重送信を防ぐ
const message = ref('')

async function createPost() {
  sending.value = true
  errors.value = {}
  message.value = ''
  try {
    const response = await api.post('/posts', form.value)
    emit('created', response.data.data) // 親（App）に「投稿できた」と知らせる
    form.value = emptyForm()             // フォームを空に戻す
    message.value = '投稿しました'
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors // 入力エラーを各欄の下に出す
    } else {
      message.value = ''
      alert('投稿に失敗しました')
      console.error('登録失敗:', e.response?.data ?? e)
    }
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
.form {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  background: #fff;
  margin-bottom: 24px;
}
.form h2 {
  margin: 0;
}
label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.9rem;
}
input,
textarea {
  font: inherit;
  padding: 6px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  align-self: flex-start;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  background: #2563eb;
  color: #fff;
  cursor: pointer;
}
button:disabled {
  opacity: 0.6;
}
.error {
  color: #b91c1c;
  margin: 0;
  font-size: 0.85rem;
}
.success {
  color: #15803d;
  margin: 0;
}
</style>
