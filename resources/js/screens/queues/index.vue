<script>
    export default {
        /**
         * The component's data.
         */
        data() {
            return {
                ready: false,
                queues: []
            };
        },


        /**
         * Prepare the component.
         */
        mounted() {
            document.title = "Api Produtos - Filas";

            this.loadQueues();
        },


        methods: {
            /**
             * Load the queues running.
             */
            loadQueues() {
                this.queues = [];
                return this.$http.get(Horizon.basePath + '/api/masters')
                    .then(response => {
                        let workers = response.data;

                        for(let worker of Object.values(workers)){
                            worker.supervisors.forEach((supervisor) => {
                                let [processSupr, supervisorName] = supervisor.name.split(":", 2);
                                this.queues.push({
                                    name: supervisor.options.queue,
                                    supervisor: supervisorName,
                                    processes: supervisor.processes[`redis:${supervisor.options.queue}`],
                                    status: supervisor.status
                                });
                            })
                        }

                        this.ready = true;
                    });
            },
            
            alterSupervisor(supervisor, command){
                return this.$http.post(Horizon.basePath + '/api/supervisors/' + supervisor, 
                    {command}
                ).then(response => {
                    
                        if(response.data.error){
                            return;
                        }

                        console.log(response.data);
                });
            }
        }
    }
</script>

<template>
    <div>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5>Filas</h5>

            </div>

            <div class="card-body">
                <div v-if="!ready" class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                        <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
                    </svg>

                    <span>Carregando...</span>
                </div>


                <div v-if="ready && queues.length == 0" class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <span>Não há nenhuma fila rodando</span>
                </div>


                <table v-if="ready && queues.length > 0" class="table table-hover table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Fila</th>
                        <th class="table-fit">Processos Rodando</th>
                        <th class="table-fit text-right">Estado</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="queue in queues">
                        <td>
                            <p>{{ queue.name }}</p>
                        </td>
                        <td>{{ queue.processes }}</td>
                        <td class="text-right">{{ {running: "Rodando", paused: "Pausado"}[queue.status] }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
