<script >
    import _ from 'lodash';
    import moment from 'moment';
    import LineChart from '../components/LineChart.vue';
    import MultipleChart from '../components/MultipleChart.vue';
    
    
    export default {
        components: {
            LineChart,
            MultipleChart
        },


        /**
         * The component's data.
         */
        data() {
            return {
                stats: {},
                workers: [],
                workload: [],
                prodsChartData: {
                    datasets: [
                        {
                            label: "Tempo",
                            data: [],
                            lineTension: 0,
                            borderColor: "#0198db",
                            borderWidth: 2,
                            order: 1,
                            backgroundColor: "#0198db",
                            borderColor: "#0198db",
                            yAxisID: 'time',
                        },
                        {
                            label: "Produtos",
                            data: [],
                            type: 'line',
                            backgroundColor: "transparent",
                            borderColor: "#b0d56d",
                            borderWidth: 2,
                            order: 0,
                            pointBackgroundColor: "#b0d56d",
                            pointBorderColor: "#b0d56d",
                            yAxisID: 'prods',
                        }
                        
                    ],
                    labels: [],                   
                },
                ready: false,
                barChart: true
            };
        },


        /**
         * Prepare the component.
         */
        mounted() {
            document.title = "Api Produtos - Dashboard";
            this.refreshStatsPeriodically();
        },


        /**
         * Clean after the component is destroyed.
         */
        destroyed() {
            clearTimeout(this.timeout);
        },


        computed: {
            /**
             * Determine the recent job period label.
             */
            recentJobsPeriod() {
                return !this.ready
                    ? 'Jobs na última hora'
                    : `Jobs no último ${this.determinePeriod(this.stats.periods.recentJobs)}`;
            },


            /**
             * Determine the recently failed job period label.
             */
            failedJobsPeriod() {
                return !this.ready
                    ? 'Jobs com falha nos últimos 7 dias'
                    : `Jobs com falha nos últimos ${this.determinePeriod(this.stats.periods.failedJobs)}`;
            },
        },


        methods: {
            /**
             * Load the general stats.
             */
            loadStats() {
                return this.$http.get(Horizon.basePath + '/api/stats')
                    .then(response => {
                        this.stats = response.data;

                        if (_.values(response.data.wait)[0]) {
                            this.stats.max_wait_time = _.values(response.data.wait)[0];
                            this.stats.max_wait_queue = _.keys(response.data.wait)[0].split(':')[1];
                        }
                        
                        this.loadChartData();
                    });
            },

            loadChartData(){
                let completedJobs = this.stats.completed_jobs;
                let med_jobs_sync = 0;
                let i = 0;
                let data1 = [];
                let data2 = [];
                let labels = [];
                for (var [date, products] of Object.entries(completedJobs)) {
                    

                    let totalTime = products.reduce((prevValue, current) => {
                        
                        return (prevValue.duration)? prevValue.duration + current.duration: prevValue + current.duration;
                    });
                    data1.push(Math.round(totalTime));
                    data2.push(products.length);
                    labels.push(`${date}`);

                    this.stats.med_sync_time = (moment.duration(totalTime, 'minutes').asSeconds() / products.length).toFixed(2);
                    med_jobs_sync += products.length;
                    i++;
                }

                med_jobs_sync = med_jobs_sync / i;

                this.stats.med_jobs_sync = Math.floor(med_jobs_sync);
                this.prodsChartData.datasets[0].data = data1;
                this.prodsChartData.datasets[1].data = data2;
                this.prodsChartData.labels = labels;
            },

            /**
             * Refresh the stats every period of time.
             */
            refreshStatsPeriodically() {
                Promise.all([
                    this.loadStats(),
                ]).then(() => {
                    this.ready = true;

                    this.timeout = setTimeout(() => {
                        this.refreshStatsPeriodically(false);
                    }, 5000);
                });
            },


            /**
             *  Count processes for the given supervisor.
             */
            countProcesses(processes) {
                return _.chain(processes).values().sum().value().toLocaleString()
            },


            /**
             *  Format the Supervisor display name.
             */
            superVisorDisplayName(supervisor, worker) {
                return _.replace(supervisor, worker + ':', '');
            },


            /**
             *
             * @returns {string}
             */
            humanTime(time) {
                
                return moment.duration(time, "seconds").humanize().replace(/^(.)|\s+(.)/g, function ($1) {
                    return $1.toUpperCase();
                });
            },


            /**
             * Determine the unit for the given timeframe.
             */
            determinePeriod(minutes) {
                return moment.duration(moment().diff(moment().subtract(minutes, "minutes"))).humanize().replace(/^An?/i, '');
            }
        }
    }
</script>

<template>
    <div>

        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title">Visão Geral</h5>

                <div class="card-tools">
                    <!-- This will cause the card to collapse when clicked -->
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>

            </div>
            <div class="card-body">
            
                <div class="d-flex">
                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-blue rounded-0">
                            <i class="fas fa-briefcase"></i>
                        </span>
                        <div class="info-box-content p-4 mb-0">
                            <small class="info-box-text text-uppercase">Média de Prods./SYNC</small>

                            <h4 class="info-box-number mt-4">
                                {{ stats.med_jobs_sync ? stats.med_jobs_sync.toLocaleString() : 0 }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-green rounded-0"><i class="far fa-clock"></i></span>
                        <div class="info-box-content p-4">
                            <small class="info-box-text text-uppercase" v-text="recentJobsPeriod"></small>

                            <h4 class="info-box-number mt-4 mb-0">
                                {{ stats.recentJobs ? stats.recentJobs.toLocaleString() : 0 }}
                            </h4>
                        </div>
                    </div>

                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-red rounded-0">
                            <i class="far fa-times-circle"></i>
                        </span>
                        <div class="info-box-content p-4">
                            <small class="info-box-text text-uppercase" v-text="failedJobsPeriod"></small>

                            <h4 class="info-box-number mt-4 mb-0">
                                {{ stats.failedJobs ? stats.failedJobs.toLocaleString() : 0 }}
                            </h4>
                        </div>
                    </div>

                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-success rounded-0" 
                            v-if="stats.status == 'running'">
                            <svg  class="" viewBox="0 0 20 20" fill="#FFF" 
                                style="width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                            </svg>
                        </span>
                        <span class="info-box-icon bg-warning" v-if="stats.status == 'paused'">
                            <svg viewBox="0 0 20 20" fill="#FFF" 
                                style="width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                            </svg>
                        </span>
                        <span class="info-box-icon bg-danger" v-if="stats.status == 'inactive'" >
                            <svg class="fill-white" viewBox="0 0 20 20" fill="#FFF" 
                                style=" width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                            </svg>
                        </span>

                        <div class="info-box-content p-4">
                            <small class="info-box-text text-uppercase">Status</small>

                            <div class="d-flex align-items-center mt-4 info-box-number">
                                <h4 class="mb-0 ml-2">{{ {running: 'Ativo', paused: 'Parado', inactive:'Inativo'}[stats.status] }}</h4>
                                <small v-if="stats.status == 'running' && stats.pausedMasters > 0" class="mb-0 ml-2">({{ stats.pausedMasters }} paused)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-secondary rounded-0">
                            <i class="fas fa-cogs"></i>
                        </span>
                        <div class="info-box-content p-4 mb-0">
                            <small class="info-box-text text-uppercase">PROCESSOS TOTAIS</small>

                            <h4 class="info-box-number mt-4">
                                {{ stats.processes ? stats.processes.toLocaleString() : 0 }}
                            </h4>
                        </div>
                    </div>

                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-yellow rounded-0 text-white">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content p-4 mb-0">
                            <small class="info-box-text text-uppercase">TEMPO MÉDIO DA Últ. SYNC</small>

                            <h4 class="info-box-number mt-4 mb-0">
                                {{ stats.med_sync_time ? `${stats.med_sync_time} segs / prod.` : '-' }}
                            </h4>
                        </div>
                    </div>

                    <div class="info-box m-1 p-0">
                        <span class="info-box-icon bg-info rounded-0">
                            <i class="fas fa-stopwatch"></i>
                        </span>
                        <div class="info-box-content p-4 mb-0">
                            <small class="info-box-text text-uppercase">Executando há mais tempo</small>

                            <h4 class="info-box-number mt-4">
                                {{ stats.queueWithMaxRuntime ? stats.queueWithMaxRuntime : '-' }}
                            </h4>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card card-primary mt-2">
            <div class="card-header">
                <h5 class="card-title">Gráfico Produtos x Dias</h5>
                <div class="card-tools">
                    <!-- This will cause the card to collapse when clicked -->
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div v-if="!ready" class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                        <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
                    </svg>

                    <span>Carregando...</span>
                </div>
                <MultipleChart v-if="ready && prodsChartData.datasets[0].data.length > 0" :chartData="prodsChartData"/>
                <h3 v-if="ready && prodsChartData.datasets[0].data.lenght <= 0"> Nenhum dado registrado ainda</h3>
            </div>
        </div>
        
        <!--
            <div class="card mt-4" v-if="workload.length">
                <div class="card-header">
                    <h5 class="card-title">Carga de Trabalho Atual</h5>
                </div>

                <table class="table table-hover table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Processos</th>
                        <th>Jobs</th>
                        <th class="text-right">Espera</th>
                    </tr>
                    </thead>

                    <tbody>
                        <template v-for="queue in workload">
                            <tr>
                                <td :class="{'font-weight-bold': queue.split_queues}">
                                    <span>{{ queue.name.replace(/,/g, ', ') }}</span>
                                </td>
                                <td :class="{'font-weight-bold': queue.split_queues}">{{ queue.processes ? queue.processes.toLocaleString() : 0 }}</td>
                                <td :class="{'font-weight-bold': queue.split_queues}">{{ queue.length ? queue.length.toLocaleString() : 0 }}</td>
                                <td :class="{'font-weight-bold': queue.split_queues}" class="text-right">{{ humanTime(queue.wait) }}</td>
                            </tr>

                            <tr v-for="split_queue in queue.split_queues">
                                <td>
                                    <svg class="icon info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M12.95 10.707l.707-.707L8 4.343 6.586 5.757 10.828 10l-4.242 4.243L8 15.657l4.95-4.95z"/>
                                    </svg>

                                    <span>{{ split_queue.name.replace(/,/g, ', ') }}</span>
                                </td>
                                <td>-</td>
                                <td>{{ split_queue.length ? split_queue.length.toLocaleString() : 0 }}</td>
                                <td class="text-right">{{ humanTime(split_queue.wait) }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="card mt-4" v-for="worker in workers" :key="worker.name">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5>{{ worker.name }}</h5>

                    <svg v-if="worker.status == 'running'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                    </svg>

                    <svg v-if="worker.status == 'paused'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                    </svg>
                </div>

                <table class="table table-hover table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Supervisor</th>
                        <th>Processos</th>
                        <th>Filas</th>
                        <th class="text-right">Balanceamento</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="supervisor in worker.supervisors">
                        <td>
                            <svg v-if="supervisor.status == 'paused'" class="fill-warning mr-1" viewBox="0 0 20 20" style="width: 1rem; height: 1rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                            </svg>
                            {{ superVisorDisplayName(supervisor.name, worker.name) }}
                        </td>
                        <td>{{ countProcesses(supervisor.processes) }}</td>
                        <td>{{ supervisor.options.queue.replace(/,/g, ', ') }}</td>
                        <td class="text-right" v-if="supervisor.options.balance">
                            ({{ supervisor.options.balance.charAt(0).toUpperCase() + supervisor.options.balance.slice(1) }})
                        </td>
                        <td class="text-right" v-else>
                            (Disabled)
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div> 
        -->

    </div>
</template>
