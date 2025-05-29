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

<h1> Send Message To Laravel Log Channel In Slack </h1>

<form action="{{url('/debug')}}" method="post">
    @csrf
    <input type="text" name="message">
    <button>Send Message</button>
</form>


</body>
</html>
