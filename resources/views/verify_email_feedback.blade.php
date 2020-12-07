<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Verify Email - {{ env('APP_NAME') }}</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

  <style>
    html,body {
      width: 100%;
      height: 100%;
      background-color: #f3f3f3;
    }

    .container {
      width: 85%;
      margin: 0 auto;
      padding-top: 5vh;
    }

    .center {
      width: 50%;
      margin: 0 auto;
    }

    .text-center {
      text-align: center;
    }

    div.button {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      position: relative;
    }

    div.button.success {
      background-color: hsl(141, 53%, 53%);
    }

    div.button.failed {
      background-color: hsl(348, 100%, 61%);
    }

    div.button .icon {
      position: absolute;
      color: white;
      top: 50%;
      left: 50%;
      transform: translateY(-50%) translateX(-50%);
    }

    .message {
      margin-top: 20px;
      font-size: 30px;
      font-weight: 500;
    }

    .message.success {
      color:  hsl(141, 53%, 53%);
    }

    .message.failed {
      color:  hsl(348, 100%, 61%);
    }

    .close-message {
      margin-top: 10px;
      font-size: 26px;
      color: hsl(0, 0%, 29%);
    }
  </style>
</head>
<body>
  <div class="container">
    {{-- Button --}}
    <div class="button center @if($success) success @else failed @endif">
      <i class="fa fa-6x icon @if($success) fa-check @else fa-times @endif"></i>
    </div>

    {{-- Message --}}
    @if ($success)
      <p class="center text-center message success">
        Success to verify your email address.
      </p>

      {{-- Close Message --}}
      <p class="text-center center close-message">
        Now you can close this page and return to your activities.
      </p>
    @else
      <p class="center text-center message failed">
        Failed to verify your email address.
      </p>
    @endif   
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
</body>
</html>