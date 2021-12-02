<script >
    import Chart from 'chart.js';

    export default {
        props: ['chartData', "options"],

        data(){
            return {
                context: null,
                chart:null
            }
        },

        mounted(){
            this.context = this.$refs.canvas.getContext('2d');

            this.chart = new Chart(this.context, {
                type: 'bar',
                options: {
                    legend: {
                        display: true,
                    },
                    scales: {
                        yAxes:[
                            {
                                id: "time",
                                type: 'linear',
                                scaleLabel: {
                                    display: true,
                                    labelString: "Tempo"
                                },
                                position: 'left',
                                ticks: {
                                    beginAtZero: true,
                                        callback: (value, index, values) => {
                                        return `${value} mins`;
                                    },
                                },
                                gridLines: {
                                    display: true
                                },
                                beforeBuildTicks: function (scale) {
                                    var max = _.max(scale.chart.data.datasets[0].data);

                                    scale.max = parseFloat(max) + parseFloat(max * 0.25);
                                },
                                
                            },
                            {
                                id: "prods",
                                type: "linear",
                                scaleLabel: {
                                    display: true,
                                    labelString: "Produtos"
                                },
                                position: "right",
                                reverse: true,
                                gridLines: {
                                    drawOnChartArea: false,
                                }
                            }
                        ],
                        xAxes: [
                            {
                                scaleLabel: {
                                    display: true,
                                    labelString: "Dia da sincronização"
                                },
                                gridLines: {
                                    display: true
                                },
                                afterTickToLabelConversion: function (data) {
                                    var xLabels = data.ticks;

                                    // xLabels.forEach(function (labels, i) {
                                    //     if (i % 6 != 0 && (i + 1) != xLabels.length) {
                                    //         xLabels[i] = '';
                                    //     }
                                    // });
                                }
                            }
                        ],
                    },
                },
                data: this.chartData
            });
        },
    }
</script>

<template>
    <div style="position: relative;">
        <canvas ref="canvas" height="120"></canvas>
    </div>
</template>
