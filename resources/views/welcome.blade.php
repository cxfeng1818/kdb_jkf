<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<script>
    var socket = new WebSocket('ws://ali.com:8035');

    socket.onopen = function() {
        console.log('WebSocket connection opened');
        socket.send('Hello, server!');
    };

    socket.onmessage = function(event) {
        console.log('Received message: ' + event.data);
    };

    socket.onclose = function() {
        console.log('WebSocket connection closed');
    };

</script>
</body>
</html>
