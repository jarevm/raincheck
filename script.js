// Calamba coordinates
var LAT = 14.2117;
var LON = 121.1653;

// ---------- weather ----------

// turn the weather code into a text and an emoji
function getWeather(code) {
    if (code <= 1) return ['Clear', '☀️'];
    if (code <= 3) return ['Cloudy', '⛅'];
    if (code <= 48) return ['Fog', '🌫️'];
    if (code <= 67) return ['Rain', '🌧️'];
    if (code <= 82) return ['Showers', '🌦️'];
    return ['Thunderstorm', '⛈️'];
}

// 13:00 -> 1 PM
function getHour(timeText) {
    var hour = Number(timeText.slice(11, 13));
    var ampm = hour < 12 ? 'AM' : 'PM';
    hour = hour % 12;
    if (hour == 0) hour = 12;
    return hour + ' ' + ampm;
}

function loadWeather() {
    var url = 'https://api.open-meteo.com/v1/forecast?latitude=' + LAT + '&longitude=' + LON +
        '&current=temperature_2m,weather_code' +
        '&hourly=precipitation_probability,precipitation,weather_code' +
        '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max' +
        '&timezone=Asia%2FManila&forecast_days=5';

    fetch(url)
        .then(function (response) { return response.json(); })
        .then(function (data) {
            showCurrent(data);
            showHourly(data);
            showDaily(data);
        })
        .catch(function () {
            document.getElementById('advisor').innerHTML = '<h2>Forecast unavailable</h2>Check your internet connection.';
            document.getElementById('current').innerHTML = '';
        });
}

function showCurrent(data) {
    var weather = getWeather(data.current.weather_code);
    var temp = Math.round(data.current.temperature_2m);
    document.getElementById('current').innerHTML =
        '<span class="temp">' + temp + '°C</span> ' + weather[1] + ' ' + weather[0];
}

function showHourly(data) {
    var hourly = data.hourly;
    var now = data.current.time.slice(0, 13) + ':00';

    // find where the current hour is in the list
    var start = 0;
    for (var i = 0; i < hourly.time.length; i++) {
        if (hourly.time[i] >= now) {
            start = i;
            break;
        }
    }

    var html = '';
    var maxChance = 0;
    var totalRain = 0;

    // show the next 6 hours
    for (var i = start; i < start + 6; i++) {
        var chance = hourly.precipitation_probability[i];
        var rain = hourly.precipitation[i];
        var icon = getWeather(hourly.weather_code[i])[1];

        if (chance > maxChance) maxChance = chance;
        totalRain = totalRain + rain;

        html += '<div class="mini">' +
            '<small>' + getHour(hourly.time[i]) + '</small>' +
            '<div>' + icon + '</div>' +
            '<b>' + chance + '%</b>' +
            '<small>' + rain + ' mm</small>' +
            '</div>';
    }

    document.getElementById('hourly').innerHTML = html;
    showAdvisor(maxChance, totalRain);
}

function showDaily(data) {
    var daily = data.daily;
    var html = '';

    for (var i = 0; i < daily.time.length; i++) {
        var date = new Date(daily.time[i] + 'T00:00');
        var day = date.toLocaleDateString('en-US', { weekday: 'short' });
        var icon = getWeather(daily.weather_code[i])[1];
        var high = Math.round(daily.temperature_2m_max[i]);
        var low = Math.round(daily.temperature_2m_min[i]);

        html += '<div class="mini">' +
            '<small>' + day + '</small>' +
            '<div>' + icon + '</div>' +
            '<b>' + high + '° / ' + low + '°</b>' +
            '<small>' + daily.precipitation_probability_max[i] + '% rain</small>' +
            '</div>';
    }

    document.getElementById('daily').innerHTML = html;
}

// umbrella advisor: Yes / Maybe / No
function showAdvisor(maxChance, totalRain) {
    var answer = 'NO';
    var message = 'No umbrella needed.';

    if (maxChance >= 60 || totalRain >= 2) {
        answer = 'YES';
        message = 'Yes, bring an umbrella!';
    } else if (maxChance >= 30) {
        answer = 'MAYBE';
        message = 'Maybe. Pack a small umbrella.';
    }

    var box = document.getElementById('advisor');
    box.className = 'advisor ' + answer;
    box.innerHTML = '<h2>☂️ Should I bring an umbrella? ' + message + '</h2>' +
        'Next 6 hours: up to ' + maxChance + '% chance of rain, about ' + totalRain.toFixed(1) + ' mm.';
}

// ---------- community reports ----------

function timeText(minutes) {
    if (minutes < 60) return minutes + ' min ago';
    if (minutes < 1440) return Math.floor(minutes / 60) + ' hr ago';
    return Math.floor(minutes / 1440) + ' day(s) ago';
}

function loadReports() {
    // build the url from the filters
    var url = 'reports.php?area=' + document.getElementById('area').value +
        '&rain=' + document.getElementById('rain').value +
        '&flood=' + document.getElementById('flood').value +
        '&date=' + document.getElementById('date').value;

    fetch(url)
        .then(function (response) { return response.json(); })
        .then(function (reports) {
            var feed = document.getElementById('feed');

            if (reports.length == 0) {
                feed.innerHTML = '<p>No reports yet. Be the first to post one.</p>';
                return;
            }

            var html = '';
            for (var i = 0; i < reports.length; i++) {
                var r = reports[i];

                var photo = '<div class="photo">No photo</div>';
                if (r.photo != '') {
                    photo = '<img class="photo" src="uploads/' + r.photo + '" alt="report photo">';
                }

                var flood = '';
                if (r.flood != 'NONE') {
                    flood = '<span class="badge ' + r.flood + '">' + r.flood + ' FLOOD</span>';
                }

                html += '<div class="report">' + photo +
                    '<div class="info">' +
                    '<span class="badge ' + r.rain + '">' + r.rain + ' RAIN</span>' + flood +
                    '<span class="badge ' + r.status + '">' + r.status + '</span>' +
                    '<h3>' + r.area + '</h3>' +
                    '<p>' + r.note + '</p>' +
                    '<small>by ' + r.author + ' · ' + timeText(r.minutes) + ' · ' + r.votes + ' confirmed</small><br>' +
                    '<button onclick="confirmReport(' + r.id + ')">I see this too</button>' +
                    '</div></div>';
            }
            feed.innerHTML = html;
        });
}

function confirmReport(id) {
    fetch('confirm.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
        .then(function (response) {
            if (response.status == 401) {
                window.location = 'login.php';
                return null;
            }
            return response.json();
        })
        .then(function (data) {
            if (data) {
                alert(data.message);
                loadReports();
            }
        });
}

// ---------- start ----------
loadWeather();
loadReports();

// refresh the feed every 15 seconds
setInterval(loadReports, 15000);
