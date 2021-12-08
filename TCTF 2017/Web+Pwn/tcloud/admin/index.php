<?php session_start(); ?>
<?php if(!isset($_SESSION['userid'],$_SESSION['username'],$_SESSION['perm'])) die(header("Location: ../")); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>TCloud Panel</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/lumen/bootstrap.min.css" rel="stylesheet">
    <!-- <link href="https://getbootstrap.com/dist/css/bootstrap-theme.min.css" rel="stylesheet"> -->

    <style>
        body {
          padding-top: 50px;
        }
        .sub-header {
          padding-bottom: 10px;
          border-bottom: 1px solid #eee;
        }
        .navbar-fixed-top {
          border: 0;
        }
        .sidebar {
          display: none;
        }
        @media (min-width: 768px) {
          .sidebar {
            position: fixed;
            top: 51px;
            bottom: 0;
            left: 0;
            z-index: 1000;
            display: block;
            padding: 20px;
            overflow-x: hidden;
            overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
            border-right: 1px solid #eee;
          }
        }

        /* Sidebar navigation */
        .nav-sidebar {
          margin-right: -21px; /* 20px padding + 1px border */
          margin-bottom: 20px;
          margin-left: -20px;
        }
        .nav-sidebar > li > a {
          padding-right: 20px;
          padding-left: 20px;
        }
        .main {
          padding: 20px;
        }
        @media (min-width: 768px) {
          .main {
            padding-right: 40px;
            padding-left: 40px;
          }
        }
        .main .page-header {
          margin-top: 0;
        }
        .placeholders {
          margin-bottom: 30px;
          text-align: center;
        }
        .placeholders h4 {
          margin-bottom: 0;
        }
        .placeholder {
          margin-bottom: 20px;
        }
        .placeholder img {
          display: inline-block;
          border-radius: 50%;
        }

    </style>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
      body {
        padding-top: 50px;
        padding-bottom: 20px;
      }
    </style>
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">TCloud Panel</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="?p=upload">Upload</a></li>
            <li><a href="?p=password">Passw*rd</a></li>
            <li><a href="?p=profile">Profile</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li class="active"><a href=".">Overview <span class="sr-only">(current)</span></a></li>
            <li><a href="?p=files">Files</a></li>
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <?php
            require '../function.php';
            require 'pages.php';
            switch(@$_GET['p']){
              case "settings":
                settings();
                break;

              case "profile":
                profile(); 
                break;

              case "upload":
                upload();
                break;

              case "download":
                download();
                break;

              case "files":
                files();
                break;

              case "password":
                password();
                break;

              case "get_password":
                get_password();
                break; 

              default:
                index();
                break;
            }
          ?> 


          <?php

          ?> 

        </div>
      </div>

      
    </div>
      
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
  </body>

</html>