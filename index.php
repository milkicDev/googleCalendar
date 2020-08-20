<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Google Calendar Api</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow bg-white mx-auto border-0" style="max-width: 600px;">
            <div class="card-body">
                <div class="text-center">
                    <h5 class="card-title mb-0 text-primary">Confirm Meeting Date & Time</h5>
                    <p class="small">Please enter the information below!</p>
                    <pre id="content" style="white-space: pre-wrap;"></pre>
                </div>

                <hr class="w-50">

                <form id="meeting" method="POST">
                    <div class="form-group">
                        <label for="name" class="text-primary">Name</label>
                        <input id="name" name="name" type="text" class="form-control border-primary" placeholder="John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="text-primary">Phone</label>
                        <input type="tel" id="phone" class="form-control border-primary" name="phone" placeholder="+381 00 000 0000" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="text-primary">Email address</label>
                        <input id="email" name="email" type="email" class="form-control border-primary" placeholder="john.doe@gmail.com" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="time" class="text-primary">Time</label>
                            <input id="time" name="time" type="time" class="form-control border-primary">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="date" class="text-primary">Date</label>
                            <input id="date" name="date" type="date" class="form-control border-primary">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="6Ld3bcEZAAAAAJ3P9qsVKjIEdVzh9H3XDtvLq4K3"></div>
                    </div>

                    <div class="text-right">
                        <button type="reset" class="btn btn-link text-danger text-uppercase">reset</button>
                        <button id="authorize_button" class="btn btn-outline-primary text-uppercase" style="display: none;" disabled="true">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js" integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg==" crossorigin="anonymous"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6Ld3bcEZAAAAAJ3P9qsVKjIEdVzh9H3XDtvLq4K3"></script>

    <script type="text/javascript">
        var timeInput = $('input#time'),
            dateInput = $('input#date');

        timeInput.val(moment().format('HH:mm'));
        dateInput.val(moment().format('yyyy-MM-DD'));
    </script>

    <script type="text/javascript">
        var CLIENT_ID = '547679470128-co8qfkb38vqjp780mm53lk7n1em0poqo.apps.googleusercontent.com';
        var API_KEY = 'AIzaSyAeEuvdEIkou3-qW7dveE-z56Jj5h9eBV4';
        var DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];
        var SCOPES = "https://www.googleapis.com/auth/calendar";

        var authorizeButton = document.getElementById('authorize_button');

        grecaptcha.ready(function() {
            grecaptcha.execute('6Ld3bcEZAAAAAJ3P9qsVKjIEdVzh9H3XDtvLq4K3', {
                action: 'submit'
            }).then(function(token) {
                authorizeButton.removeAttribute('disabled');
            });
        });

        /**
         *  On load, called to load the auth2 library and API client library.
         */
        function handleClientLoad() {
            gapi.load('client:auth2', initClient);
        }

        /**
         *  Initializes the API client library and sets up sign-in state
         *  listeners.
         */
        function initClient() {
            gapi.client.init({
                apiKey: API_KEY,
                clientId: CLIENT_ID,
                discoveryDocs: DISCOVERY_DOCS,
                scope: SCOPES
            }).then(function() {
                // Listen for sign-in state changes.
                gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

                // Handle the initial sign-in state.
                updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
                authorizeButton.onclick = handleAuthClick;
            }, function(error) {
                appendPre(JSON.stringify(error, null, 2));
            });
        }

        /**
         *  Called when the signed in status changes, to update the UI
         *  appropriately. After a sign-in, the API is called.
         */
        function updateSigninStatus(isSignedIn) {
            if (isSignedIn) {
                // authorizeButton.style.display = 'none';
                addEvent();
            } else {
                authorizeButton.style.display = 'inline-block';
                authorizeButton.setAttribute('disabled', true);
            }
        }

        /**
         *  Sign in the user upon button click.
         */
        function handleAuthClick(event) {
            gapi.auth2.getAuthInstance().signIn();
        }

        /**
         *  Sign out the user upon button click.
         */
        function handleSignoutClick(event) {
            gapi.auth2.getAuthInstance().signOut();
        }

        /**
         * Append a pre element to the body containing the given message
         * as its text node. Used to display the results of the API call.
         *
         * @param {string} message Text to be placed in pre element.
         */
        function appendPre(message) {
            var pre = document.getElementById('content');
            var textContent = document.createTextNode(message + '\n');
            pre.appendChild(textContent);
        }

        function addEvent() {
            var name = $('input#name').val(),
                phone = $('input#phone').val(),
                email = $('input#email').val(),
                timeStart = timeInput.val(),
                dateStart = dateInput.val();

            var event = {
                'summary': 'Meeting with ' + name,
                'description': phone,
                'start': {
                    'dateTime': moment(dateStart + " " + timeStart, 'yyyy-MM-DD HH:mm').toISOString(),
                    'timeZone': 'Europe/Belgrade'
                },
                'end': {
                    'dateTime': moment(dateStart + " " + timeStart, 'yyyy-MM-DD HH:mm').add(1, 'd').toISOString(),
                    'timeZone': 'Europe/Belgrade'
                },
                'recurrence': [
                    'RRULE:FREQ=DAILY;COUNT=2'
                ],
                'attendees': [{
                    'email': 'lltms3580@gmail.com'
                }],
                'reminders': {
                    'useDefault': false,
                    'overrides': [{
                            'method': 'email',
                            'minutes': 15
                        },
                        {
                            'method': 'email',
                            'minutes': 30
                        },
                        {
                            'method': 'popup',
                            'minutes': 15
                        },
                        {
                            'method': 'popup',
                            'minutes': 30
                        },
                    ]
                },
                "sendNotifications": true,
                "sendUpdates": "all",
            };

            if (name !== '' && email !== '' && phone !== '') {
                var request = gapi.client.calendar.events.insert({
                    'calendarId': 'primary',
                    'resource': event
                });

                request.execute(function(event) {
                    appendPre('Event created successful!');
                });
            } else {
                appendPre('Please fill all fields');
            }
        }
    </script>

    <script async defer src="https://apis.google.com/js/api.js" onload="this.onload=function(){};handleClientLoad()" onreadystatechange="if (this.readyState === 'complete') this.onload()">
    </script>
</body>

</html>
