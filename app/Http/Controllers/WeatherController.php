<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{

    public function getWeatherForecast(Request $postData)
    {
        $result = array();
        $city = $postData->city[0];

        $request =  Http::get("https://api.openweathermap.org/data/2.5/forecast", [
            'q' => $city,
            'appid' => '49451957cdc8877270522ee88f15f5f3'
        ]);

        if ($request->json()['cod'] == 200) {
            $result['success'] = true;
            $foreCastList = $request->json('list');

            $closestDateTimeToCurrent = $this->findClosestDateTimeToCurrent($foreCastList);
            $mostUpdatedForecastDisplay = $this->mostUpdatedForecastHtmlBuilder($foreCastList, $closestDateTimeToCurrent, $city);
            $todaysForecastHtmlDisplay = $this->todaysForecastHtmlBuilder($foreCastList);
            $fiveDaysForecastHtmlDisplay = $this->fiveDaysForecastHtmlBuilder($foreCastList);

            //city spots image carousel
            $citySpotsCarouselHtmlDisplay = $this->citySpotsCarouselHtmlBuilder($city);

            $result['mostUpdatedForecastDisplay'] = $mostUpdatedForecastDisplay;
            $result['todaysForecastHtmlDisplay'] = $todaysForecastHtmlDisplay;
            $result['fiveDaysForecastHtmlDisplay'] = $fiveDaysForecastHtmlDisplay;
            $result['citySpotsCarouselHtmlDisplay'] = $citySpotsCarouselHtmlDisplay;
        } else {
            $result['success'] = false;
        }

        return json_encode($result);
    }

    public function findClosestDateTimeToCurrent($list)
    {
        $dates = array();
        $currentDateTime = date('Y-m-d H:i:s');

        if (!empty($list)) {
            foreach ($list as $row) {
                if (date('Y-m-d') == date('Y-m-d', strtotime($row['dt_txt']))) {
                    $dates[] = $row['dt_txt'];
                }
            }
        }
        foreach ($dates as $day) {
            $interval[] = abs(strtotime($currentDateTime) - strtotime($day));
        }
        asort($interval);
        $closest = key($interval);
        return $dates[$closest];
    }

    public function mostUpdatedForecastHtmlBuilder($list, $closest, $city)
    {
        $html = "";
        if (!empty($list)) {
            foreach ($list as $row) {
                if ($row['dt_txt'] == $closest) {
                    $icon =  url("assets/icons/" . $row['weather'][0]['icon'] . ".png");
                    $html .= '<div class="row">';
                    $html .= '<div class="col-12">';
                    $html .= '<h2 class="mt-2">';
                    $html .= '<span class="float-start city-name">
                                    ' . $city . '<br/>
                                        <span class="weather-description gray-text">' . ucwords($row['weather'][0]['description']) . '</span>
                                    </span>
                                    <img src="' . $icon . '" class="float-end current-forecast-icon" alt="description" />
                                    <div class="clearfix"></div>';
                    $html .= '</h2>';
                    $html .= '<h3>' . $this->kelvinToCelcius($row['main']['temp']) . '</h3>';
                    $html .= '<p><b>' . date('D', strtotime($closest)) . '</b>, ' . date('M d, Y h:i A', strtotime($closest)) . '</p>';
                    $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<div class="row mt-3">';
                    $html .= '<div class="col-sm-6">';
                    $html .= '
                            <table class="table">
                            <tr>
                                <td> <i class="fa-solid fa-temperature-three-quarters"></i> Feels Like</td>
                                <td>' . $this->kelvinToCelcius($row['main']['feels_like']) . '</td>
                            </tr>
                            <tr>
                                <td> <i class="fa-solid fa-droplet"></i> Humidity</td>
                                <td>' . $row['main']['humidity'] . '%</td>
                            </tr>
                            <tr>
                                <td> <i class="fa-solid fa-wind"></i> Wind</td>
                                <td>' . $this->mpsToKph($row['wind']['speed']) . '</td>
                            </tr>
                        </table>';
                    $html .= '</div>';
                    $html .= '<div class="col-sm-6">';
                    $html .= '
                            <table class="table">
                                <tr>
                                    <td> <i class="fa-solid fa-eye"></i> Visibility</td>
                                    <td>' . $this->mToKm($row['visibility']) . '</td>
                                </tr>
                                <tr>
                                    <td> <i class="fa-solid fa-temperature-three-quarters"></i> Max Temp</td>
                                    <td>' . $this->kelvinToCelcius($row['main']['temp_max']) . '</td>
                                </tr>
                                <tr>
                                    <td> <i class="fa-solid fa-temperature-three-quarters"></i> Min Temp</td>
                                    <td>' . $this->kelvinToCelcius($row['main']['temp_min']) . '</td>
                                </tr>
                            </table>
                        ';
                    $html .= '</div>';
                    $html .= '</div>';
                }
            }
        }

        return $html;
    }

    public function todaysForecastHtmlBuilder($list)
    {
        $html = "";
        $html .= '<div class="row">';
        if (!empty($list)) {
            foreach ($list as $row) {
                //check if today
                if (date('Y-m-d', strtotime($row['dt_txt'])) == date('Y-m-d')) {
                    $icon =  url("assets/icons/" . $row['weather'][0]['icon'] . ".png");

                    $html .= '<div class="col-lg-2 col-md-4 col-6">';
                    $html .= '<div class="forecast-item shadow-lg">';
                    $html .= '<center>';
                    $html .= '<span class="gray-text forecast-item-label"><b>' . date('h:i A', strtotime($row['dt_txt'])) . '</b></span>';
                    $html .= '<img src="' . $icon . '" class="d-block forecast-item-img" alt="description" />';
                    $html .= '<h5 class="gray-text">' . $this->kelvinToCelcius($row['main']['temp']) . '</h5>';
                    $html .= '</center>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
            }
        }
        $html .= '</div>';

        return $html;
    }

    public function fiveDaysForecastHtmlBuilder($list)
    {
        $html = "";
        if (!empty($list)) {
            $currentDate = "";
            foreach ($list as $row) {
                if ($currentDate != date('Y-m-d', strtotime($row['dt_txt'])) && date('Y-m-d') != date('Y-m-d', strtotime($row['dt_txt']))) {
                    $icon =  url("assets/icons/small-icons/" . $row['weather'][0]['icon'] . ".png");

                    $html .= '<tr>
                        <td>' . date('D m/d', strtotime($row['dt_txt'])) . '</td>
                        <td><center><img src="' . $icon . '" class="d-block five-day-image" alt="description" /></center></td>
                        <td>' . $this->kelvinToCelcius($row['main']['temp']) . '</td>
                    </tr>';
                }
                $currentDate = date('Y-m-d', strtotime($row['dt_txt']));
            }
        }
        return $html;
    }

    public function kelvinToCelcius($kelvin)
    {
        $celsius = $kelvin - 273.15;
        return round($celsius) . "&#176;C";
    }

    public function kelvinToFahrenheit($kelvin)
    {
        $fahrenheit = 9 / 5 * ($kelvin - 273.15) + 32;
        return round($fahrenheit) . "&#176;F";
    }

    public function mpsToKph($mps)
    {
        return round((3.6 * $mps)) . " km/h";
    }

    public function mToKm($m)
    {
        return round($m / 1000) . " km";
    }

    public function citySpotsCarouselHtmlBuilder($city)
    {
        $html = "";
        $request =  Http::withHeaders([
            'Authorization' => 'fsq3h3is9+Hs/5looOb7+YAbBkIF6guKXrkqOcP54PeDHpo=',
            'accept' => 'application/json',
        ])->get("https://api.foursquare.com/v3/places/search?categories=16000&near=" . $city . "&limit=5");

        $spots = $request->json('results');
        $count = 0;
        if (!empty($spots)) {
            foreach ($spots as $spot) {
                $count++;
                $id = $spot['fsq_id'];
                $category = $spot['categories'][0]['name'];
                $spotName = $spot['name'];
                //get image

                $imageRequest = Http::withHeaders([
                    'Authorization' => 'fsq3h3is9+Hs/5looOb7+YAbBkIF6guKXrkqOcP54PeDHpo=',
                    'accept' => 'application/json',
                ])->get("https://api.foursquare.com/v3/places/" . $id . "/photos?limit=5");

                $spotImageData = $imageRequest->json()[1];
                $spotImageUrl = $spotImageData['prefix'] . "original" . $spotImageData['suffix'];

                $active = ($count == 1) ? "active" : "";
                $html .= '
                <div class="carousel-item ' . $active . '">
                    <img src="' . url('assets/images/loading-img.gif') . '" data-original="' . $spotImageUrl . '" class="carousel-img d-block lazy" alt="' . $spotName . '">
                    <div class="card">
                        <div class="card-body">
                            <h5>' . $spotName . '</h5>
                            <span><small>' . $category . '</small></span>
                        </div>
                    </div>
                </div>
                ';
            }
        }

        return $html;
    }
}
