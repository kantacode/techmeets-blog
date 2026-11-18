<template>
  <main class="container">
    <h1>techmeets-blog</h1>
    <PostForm @created="onCreated" />
    <PostList ref="postList" />
  </main>
</template>

<script setup>
// 画面全体の組み立てだけを担当する：フォームと一覧を並べ、投稿できたら一覧を取り直す
import { ref } from 'vue'
import PostForm from './components/PostForm.vue'
import PostList from './components/PostList.vue'

const postList = ref(null) // PostList コンポーネントへの参照

function onCreated() {
  postList.value.fetchPosts() // 保存後に一覧を再取得して画面に反映する
}
</script>

<style scoped>
.container {
  max-width: 720px;
  margin: 0 auto;
  padding: 24px 16px;
}
</style>
