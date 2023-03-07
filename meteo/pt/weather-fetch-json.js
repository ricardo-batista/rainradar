const fs = require('fs')
const request = require('request')

const now = new Date()

const MAX_FORECAST_ITEMS = 7
const MAX_HOURLY_ITEMS = 10
const localPath = './weather/'

const weatherURL = 'http://api.ipma.pt/public-data/forecast/aggregate/'
const weatherLocations = [ 
    { filename: '0.json', city: 'Aveiro', jsonURL: '1010500.json' },
    { filename: '1.json', city: 'Beja', jsonURL: '1020500.json' },
    { filename: '2.json', city: 'Braga', jsonURL: '1030300.json' },
    { filename: '3.json', city: 'Bragança', jsonURL: '1040200.json' },
    { filename: '4.json', city: 'Castelo Branco', jsonURL: '1050200.json' },
    { filename: '5.json', city: 'Coimbra', jsonURL: '1060300.json' },
    { filename: '6.json', city: 'Évora', jsonURL: '1070500.json' },
    { filename: '7.json', city: 'Faro', jsonURL: '1080500.json' },
    { filename: '8.json', city: 'Guarda', jsonURL: '1090700.json' },
    { filename: '9.json', city: 'Leiria', jsonURL: '1100900.json' },
    { filename: '10.json', city: 'Lisboa', jsonURL: '1110600.json' },
    { filename: '11.json', city: 'Portalegre', jsonURL: '1121400.json' },
    { filename: '12.json', city: 'Porto', jsonURL: '1131200.json' },
    { filename: '13.json', city: 'Santarém', jsonURL: '1141600.json' },
    { filename: '14.json', city: 'Setúbal', jsonURL: '1141600.json' },
    { filename: '15.json', city: 'Viana do Castelo', jsonURL: '1160900.json' },
    { filename: '16.json', city: 'Vila Real', jsonURL: '1171400.json' },
    { filename: '17.json', city: 'Viseu', jsonURL: '1182300.json' },
    { filename: '18.json', city: 'Funchal', jsonURL: '2310300.json' },
    { filename: '19.json', city: 'Ponta Delgada', jsonURL: '3420300.json' },
    { filename: '20.json', city: 'Caldas da Rainha', jsonURL: '1100600.json' }
]

// get weather json for all locations
weatherLocations.forEach(fetchJSON)
function fetchJSON(location, index) {
    console.log('fetchJSON location ' + location.city)
    request(weatherURL+weatherLocations[index].jsonURL, function (error, response, body) {
        if (!error) {
            parseWeatherJSON(JSON.parse(body), index);
        } else {
            console.log(error);
        }
    });    
}

// parse weather json 
var parseWeatherJSON = function(json, index) {

    // main template
    var weatherJSON = {
        data: {
            temperature: "0",
            feels_like: "0",
            wind_speed: "0",
            wind_degree: "0",
            humidity: "0",
            visibility: "0",
            pressure: "0",
            code: "113",
            city: weatherLocations[index].city,
            forecast: [],
            hourly: []
        }
    }

    var forecastItemJSON = {
        date: "",
        max: "",
        min: "",
        rain: "",
        rain_unit: "%",
        code: "",
        sunrise: "",
        sunset: "",
        moonrise: "",
        moonset: "",
        uvIndex: ""
    }

    var hourlyItemJSON = {
        hour: "00:00",
        temperature: "0",
        wind_speed: "0",
        wind_degree: "0",
        code: "113",
        rain: "0",
        rain_unit: "%"
    }    

    json.forEach(parseForecastAndHourlyWeather)
    function parseForecastAndHourlyWeather(itemJSON, index) {

        // forecast
        if (itemJSON.hasOwnProperty('tMin') && itemJSON.hasOwnProperty('tMax')) {
                        
            if (weatherJSON.data.forecast.length < MAX_FORECAST_ITEMS) {
                let forecastItem = JSON.parse(JSON.stringify(forecastItemJSON))
                forecastItem.date = itemJSON.dataPrev.replace("T00:00:00", "")
                forecastItem.max = ""+Math.round(itemJSON.tMax)
                forecastItem.min = ""+Math.round(itemJSON.tMin)
                forecastItem.rain = ""+Math.round(sanitizeRainValue(itemJSON.probabilidadePrecipita))
                forecastItem.code = getWeatherCode(weatherJSON.data.city, itemJSON.idTipoTempo)
                weatherJSON.data.forecast.push(forecastItem)
            }
        }
            
        // hourly
        if (itemJSON.hasOwnProperty('tMed')) {
            let itemDate = Date.parse(itemJSON.dataPrev)
            
            // update current weather with the latest item
            if (itemDate < now) {
                weatherJSON.data.temperature = ""+Math.round(itemJSON.tMed)
                weatherJSON.data.feels_like = ""+Math.round(itemJSON.utci)
                weatherJSON.data.wind_speed = ""+Math.round(itemJSON.ffVento)
                weatherJSON.data.wind_degree = itemJSON.ddVento
                weatherJSON.data.humidity = itemJSON.hr
                weatherJSON.data.code = getWeatherCode(weatherJSON.data.city, itemJSON.idTipoTempo)
            }

            // add to next hours
            else if (weatherJSON.data.hourly.length < MAX_HOURLY_ITEMS) {
                let hourlyItem = JSON.parse(JSON.stringify(hourlyItemJSON))
                hourlyItem.hour = itemJSON.dataPrev.substring(itemJSON.dataPrev.indexOf("T")+1,itemJSON.dataPrev.indexOf("T")+6)
                hourlyItem.temperature = ""+Math.round(itemJSON.tMed)
                hourlyItem.wind_speed = itemJSON.ffVento
                hourlyItem.wind_degree = itemJSON.ddVento
                hourlyItem.code = getWeatherCode(weatherJSON.data.city, itemJSON.idTipoTempo)
                hourlyItem.rain = ""+Math.round(sanitizeRainValue(itemJSON.probabilidadePrecipita))
                weatherJSON.data.hourly.push(hourlyItem)
            }
        }
    }


    console.log('Saving json for ' + weatherLocations[index].city)
    fs.writeFileSync(localPath + "weather." + weatherLocations[index].filename, JSON.stringify(weatherJSON));
}

function sanitizeRainValue(rainValue) {
    if (rainValue < 0) {
        return "0"
    } else if (rainValue > 100) {
        return "100"
    } else {
        return rainValue
    }
}

function getWeatherCode(city, idTipoTempo) {
    if (weatherCodes.hasOwnProperty(idTipoTempo)) {
        return weatherCodes[idTipoTempo]
    } else {
        console.log("getWeatherCode " + city + " doesn't have " + idTipoTempo)
        return weatherCodes["1"]
    }
}

const weatherCodes = {
    "1": "113",  // clear
    "2": "116",  // partly cloudy
    "3": "116",  // partly cloudy
    "4": "122", // cloudy
    "5": "119",  // cloudy
    "6": "266", // aguaceiros
    "7": "176", // aguaceiros fracos
    "9": "266", // aguaceiros
    "10": "176", // aguaceiros fracos
}
