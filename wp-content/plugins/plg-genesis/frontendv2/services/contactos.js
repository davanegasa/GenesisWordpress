import { api } from '../api/client.js';

export async function buscar(q, limit=20, offset=0){
    const res = await api.get('/contactos?q='+encodeURIComponent(q||'')+'&limit='+limit+'&offset='+offset);
    return (res && res.data && res.data.items) || [];
}


