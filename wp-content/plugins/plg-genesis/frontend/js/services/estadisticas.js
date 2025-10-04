(function(){
	if (!window.PlgGenesisApiClient) return;
	window.PlgGenesisEstadisticasService = {
		resumen: async function(month, year){
			const params = new URLSearchParams();
			if (month) params.set('month', month);
			if (year) params.set('year', year);
			const qs = params.toString() ? `?${params.toString()}` : '';
			return await window.PlgGenesisApiClient.get(`/estadisticas${qs}`);
		}
	};
})();