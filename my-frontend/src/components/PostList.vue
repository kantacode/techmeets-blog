<template>
  <section>
    <h2>投稿一覧</h2>
    <p v-if="loading">読み込み中...</p>
    <p v-else-if="error" class="error">投稿を取得できませんでした（Laravel が起動しているか確認してください）</p>
    <p v-else-if="posts.length === 0">まだ投稿がありません</p>
    <div v-else>
      <PostCard v-for="post in posts" :key="post.id" :post="post" />
    </div>
  </section>
</template>

<script setup>
// 一覧全体を担当する：API から投稿を取ってきて、1件ずつ PostCard に渡す
import { ref, onMounted } from 'vue'
import api from '../api'
import PostCard from './PostCard.vue'

const posts = ref([])
const loading = ref(true)
const error = ref(false)

async function fetchPosts() {
  loading.value = true
  error.value = false
  try {
    const response = await api.get('/posts')
    posts.value = response.data.data // API リソースの中身は data キーに入っている
  } catch (e) {
    error.value = true
    console.error('取得失敗:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPosts) // 画面に表示されたら1回取得する

// 親（App）から「もう一度取得して」と呼べるように公開する
defineExpose({ fetchPosts })
</script>

<style scoped>
.error {
  color: #b91c1c;
}
</style>
