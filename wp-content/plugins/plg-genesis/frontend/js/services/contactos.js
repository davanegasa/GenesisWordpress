(function(){
	if (!window.PlgGenesisApiClient) return;
	window.PlgGenesisContactosService = {
		buscar: async function(q, limit = 20, offset = 0){
			const params = new URLSearchParams();
			if (q != null) params.set('q', q);
			params.set('limit', String(limit));
			params.set('offset', String(offset));
			const qs = `?${params.toString()}`;
			return await window.PlgGenesisApiClient.get(`/contactos${qs}`);
		}
	};
})();