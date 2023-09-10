@extends('layout')

@section('styles')
    <link href="{{ asset('assets/css/autocomplete.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="app-header">
        <h1 class="text-bold">
            <img src="{{ asset('assets/images/flag.png') }}" class="app-icon shadow" alt="Japan Flag" />
            Japan Weather
        </h1>
        <div class="container-fluid">
            <form method="POST" id="searchForm">
                <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <center>
                            <div class="form-group">
                                <select id="cityDropdown" multiple>
                                    <option value='Tokyo'>Tokyo</option>
                                    <option value='Osaka'>Osaka</option>
                                    <option value='Nagoya'>Nagoya</option>
                                    <option value='Yokohama'>Yokohama</option>
                                    <option value='Fukuoka'>Fukuoka</option>
                                    <option value='Sapporo'>Sapporo</option>
                                    <option value='Kawasaki'>Kawasaki</option>
                                    <option value='Kobe'>Kobe</option>
                                    <option value='Kyoto'>Kyoto</option>
                                    <option value='Saitama'>Saitama</option>
                                    <option value='Hiroshima'>Hiroshima</option>
                                    <option value='Sendai'>Sendai</option>
                                    <option value='Setagaya'>Setagaya</option>
                                    <option value='Nerima'>Nerima</option>
                                    <option value='Edogawa'>Edogawa</option>
                                    <option value='Adachi'>Adachi</option>
                                    <option value='Itabashi'>Itabashi</option>
                                    <option value='Toyonaka'>Toyonaka</option>
                                    <option value='Shinjuku'>Shinjuku</option>
                                    <option value='Nakano'>Nakano</option>
                                    <option value='Toshima'>Toshima</option>
                                    <option value='Meguro'>Meguro</option>
                                    <option value='Sumida'>Sumida</option>
                                    <option value='Minato'>Minato</option>
                                    <option value='Arakawa'>Arakawa</option>
                                    <option value='Taito'>Taito</option>
                                    <option value='Nishitokyo'>Nishitokyo</option>
                                    <option value='Kamirenjaku'>Kamirenjaku</option>
                                    <option value='Musashino'>Musashino</option>
                                    <option value='Moriguchi'>Moriguchi</option>
                                    <option value='Kokubunji'>Kokubunji</option>
                                    <option value='Koganei'>Koganei</option>
                                    <option value='Shibuya'>Shibuya</option>
                                    <option value='Komae'>Komae</option>
                                </select>
                            </div>
                        </center>
                    </div>
                    <div class="col-lg-3"></div>
                </div>
            </form> 
        </div>  
    </div>
    <div class="app-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="current-forecast shadow-lg">
                               
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="five-day-forecast shadow-lg">
                                <h5>5-Day Forecast</h5>
                                <table class="table mt-2 five-day-table">
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <span class="text-white mb-1  mt-2"><b><small>Today's Forecast</small></b></span>
                    <div class="today-forecast">
                        
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="city-info shadow">
                        <h5 class="text-white">Places you might want to go</h5>
                        <div id="citySpotsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                            <div class="carousel-inner">
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#citySpotsCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#citySpotsCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/autocomplete.min.js') }}"></script>
    <script>
    
        var loadingOverlay = document.querySelector('.loading');

        function toggleLoading(){
            if (loadingOverlay.classList.contains('hidden')){
                loadingOverlay.classList.remove('hidden');
            } else {
                loadingOverlay.classList.add('hidden');
            }
        }

        $('#cityDropdown').autocompleteDropdown({
            customPlaceholderText:"Search for city",
            onChange:function() {
                $(".app-header").css('top',0);
                $(".app-body").hide();
                $.ajax({
                    url: "{{ url('get-weather-forecast') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        city:$("#cityDropdown").val()
                    },
                    dataType: 'JSON',
                    beforeSend: function(){
                        toggleLoading();
                    },
                    success: function(data){
                        toggleLoading();
                        if(data.success){
                            setTimeout(function(){
                                $(".start-up-display").hide();
                                $(".current-forecast").html(data.mostUpdatedForecastDisplay);
                                $(".today-forecast").html(data.todaysForecastHtmlDisplay);
                                $(".five-day-table tbody").html(data.fiveDaysForecastHtmlDisplay);
                                $("#citySpotsCarousel .carousel-inner").html(data.citySpotsCarouselHtmlDisplay);
                                $(".lazy").lazyload();
                                $(".app-body").show();
                            },500);
                        }else{
                            $(".app-header").css('top',"250px");  
                            $(".app-body").hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'No Data Found',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            }
        });

        $(document).ready(function(){
            $('#cityDropdown').val("Tokyo").trigger('change');
        });
    </script>
@endsection
