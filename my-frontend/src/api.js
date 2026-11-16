import axios from 'axios'

// Laravel（Docker の nginx：localhost の80番ポート）の API を呼ぶための axios
// URL を変えたいときは my-frontend/.env に VITE_API_URL=... を書く
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:80/api',
  headers: { Accept: 'application/json' }, // エラーも HTML ではなく JSON で返してもらう
})

export default api
