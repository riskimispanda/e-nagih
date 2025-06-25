@extends('layouts/contentNavbarLayout')
@section('title', 'Mind Mapping')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')
@endsection

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Mind Mapping</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Network Mapping</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div id="mindmap-container" style="width: 100%; height: 600px; overflow: hidden;">
                                <div id="mindmap" class="card bg-black rounded bg-opacity-10"
                                    style="width: 100%; height: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var chartDom = document.getElementById('mindmap');
                            var myChart = echarts.init(chartDom);
                            var mindMapData = {!! json_encode($mind_map ?? []) !!};
                            var container = document.getElementById('mindmap-container');

                            // Initialize PerfectScrollbar
                            new PerfectScrollbar(container, {
                                wheelPropagation: true
                            });

                            if (!mindMapData || mindMapData.length === 0) {
                                console.error('Mind map data kosong');
                            }

                            var option = {
                                title: {
                                    text: '© E-Nagih by Panda',
                                    left: 'right',
                                    bottom: '0',
                                    textStyle: {
                                        fontSize: 12,
                                        color: '#666'
                                    }
                                },
                                tooltip: {
                                    trigger: 'item',
                                    formatter: function(params) {
                                        var result = '';
                                        if (params.data) {
                                            if (params.data.ip_address) {
                                                result = '<strong>' + params.data.name + '</strong><br/>';
                                                result += 'Lokasi: ' + params.data.name + '<br/>';
                                                result += 'IP Address: ' + params.data.ip_address + '<br/>';
                                            } else if (params.data.nama_lokasi) {
                                                result = '<strong>OLT</strong><br/>';
                                                result += 'Nama: ' + params.data.nama_lokasi + '<br/>';
                                            } else if (params.data.nama_odc) {
                                                result = '<strong>ODC</strong><br/>';
                                                result += 'Nama: ' + params.data.nama_odc + '<br/>';
                                            } else if (params.data.nama_odp) {
                                                result = '<strong>ODP</strong><br/>';
                                                result += 'Nama: ' + params.data.nama_odp + '<br/>';
                                            } else {
                                                result = '<strong>' + params.data.name + '</strong>';
                                            }
                                        } else {
                                            result = 'Data tidak tersedia';
                                        }
                                        return result;
                                    }
                                },
                                series: [{
                                    type: 'tree',
                                    data: [mindMapData],
                                    top: '5%',
                                    left: '50%',
                                    bottom: '5%',
                                    right: '20%',
                                    symbolSize: [100, 50],
                                    initialTreeDepth: 0,
                                    layout: 'orthogonal',
                                    roam: true,
                                    expandAndCollapse: true,
                                    animationDurationUpdate: 150,
                                    animationEasingUpdate: 'quinticInOut',
                                    itemStyle: {
                                        color: '#FFD63A',
                                        borderColor: '#000',
                                        borderWidth: 2,
                                    },
                                    label: {
                                        align: 'center',
                                        fontSize: 10,
                                        fontWeight: 'bold',
                                        fontFamily: 'Arial',
                                        color: '#000'
                                    },
                                    orient: 'LR',
                                    lineStyle: {
                                        color: '#000',
                                        width: 2
                                    }
                                }]
                            };

                            myChart.setOption(option);
                            window.addEventListener('resize', function() {
                                myChart.resize();
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
