<template>
  <article class="card">
    <h3>{{ post.title }}</h3>
    <p class="meta">
      <span class="category">{{ post.category }}</span>
      投稿者：{{ post.user.name }} ・ {{ post.created_at }}
    </p>
    <p class="content">{{ post.content }}</p>
  </article>
</template>

<script setup>
// 投稿1件分の表示だけを担当する。データは親（PostList）から props で受け取る
defineProps({
  post: { type: Object, required: true },
})
</script>

<style scoped>
.card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
  background: #fff;
}
.card h3 {
  margin: 0 0 4px;
}
.meta {
  color: #666;
  font-size: 0.85rem;
  margin: 0 0 8px;
}
.category {
  background: #eef2ff;
  color: #3730a3;
  border-radius: 4px;
  padding: 2px 6px;
  margin-right: 6px;
}
.content {
  white-space: pre-wrap; /* 改行をそのまま表示する */
  margin: 0;
}
</style>
