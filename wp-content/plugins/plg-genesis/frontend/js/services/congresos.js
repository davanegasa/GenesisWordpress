(function(){
	if (!window.PlgGenesisApiClient) return;
	window.PlgGenesisCongresosService = {
		listar: async function(){
			return await window.PlgGenesisApiClient.get(`/congresos`);
		}
	};
})();