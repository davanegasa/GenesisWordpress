(function(){
	if (!window.PlgGenesisApiClient) return;

	window.PlgGenesisEstudiantesService = {
		listarPorContacto: async function(contactoId) {
			if (!contactoId) throw new Error('contactoId es requerido');
			return await window.PlgGenesisApiClient.get(`/estudiantes?contactoId=${encodeURIComponent(contactoId)}`);
		},
		crear: async function(payload){
			return await window.PlgGenesisApiClient.post(`/estudiantes`, payload);
		},
		obtener: async function(id){
			return await window.PlgGenesisApiClient.get(`/estudiantes/${encodeURIComponent(id)}`);
		},
		actualizar: async function(id, payload){
			return await window.PlgGenesisApiClient.put(`/estudiantes/${encodeURIComponent(id)}`, payload);
		}
	};
})();