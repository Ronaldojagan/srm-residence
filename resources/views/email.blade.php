<!DOCTYPE html>
<html>
<head>
    <title>Request a User Delete</title>
</head>
<body>
    <h5>Request a User Delete</h5>
    <p>User name : {{ $mailData['username'] }}</p>
    <p>Phone No :  {{ $mailData['phone'] }}</p>

    <p>Email : {{ $mailData['email'] }}</p>
    <p>Reason : {{ $mailData['reason'] }}</p>

    <p>Thank you</p>
</body>
</html>
