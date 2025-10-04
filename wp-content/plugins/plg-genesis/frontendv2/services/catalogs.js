import { api } from '../api/client.js';

let cache = null;
export async function getCatalogs(){
	if (cache) return cache;
	try {
		const res = await api.get('/catalogs');
		cache = (res && res.data) || {};
		return cache;
	} catch(e){
		cache = { civilStatus: [], educationLevel: [] };
		return cache;
	}
}