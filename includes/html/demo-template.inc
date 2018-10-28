<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">

    <title>Non-Framework PHP Example</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    

  </head>
  <body>


    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/countries/">Non-Framework PHP Example</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="https://www.shortdark.net/">Shortdark</a></li>
          </ul>
        </div>
      </div>
    </div>
    



    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li></li>
            <li><a href="/countries/">Pie Chart and Table</a></li>
            <li><a href="/countries/world-map.html">World Map</a></li>
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

          <h2 class="sub-header"><?php echo $title; ?></h2>
          <div class="table-responsive" id="contenthome">
            <?php print $content; ?>
            <?php print $demo->output_error_string; ?>

          </div>
        </div>
      </div>
    </div>
    
    <!-- The DIV for the loading function //-->
    <div class="modal"></div>

  </body>

  <script
          src="https://code.jquery.com/jquery-3.3.1.min.js"
          integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
          crossorigin="anonymous"></script>

  <?php if(true == $demo->mapscript){ ?>
      <script>
          $(".test").before("<p>Darker red means higher volume of units</p>");
      </script>
  <?php } ?>

</html>
