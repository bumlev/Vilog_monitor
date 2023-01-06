<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./css/style.css" />
    <script src="//code.jquery.com/jquery-1.12.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Vilog</title>
  </head>
  <body>
    <?php 
      include_once 'visitors.class.php';
      $datas = $visitors->identifyVisitor($_GET['id']);
    ?>
    <div id="container" class="container">
        <form action="#" id="signin" class="signin">
            <h1>Set visitor as User</h1>
            <input type="text" style="display:none" value ="<?=md5($datas[0])?>" name="visitor">
            <input placeholder="Enter password ..." type="password" name="password">
            <span id="error_password" class="error"></span>
            
            <div class="form-row">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="admin" value="option1">
                <label class="form-check-label label1" for="admin">Admin</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="user" value="option2">
                <label class="form-check-label label2" for="user">User</label>
              </div>
            </div>
            <span id="error_radio" class="error"></span>
            <button id="beUser" class="register"> Be a User</button>
        </form> 
    </div>

    <script>
      $(document).ready(function(){    
        $('#beUser').on('click' , function(e){
            e.preventDefault();
            var datas = {
              visitor:$("input[name='visitor']").val(),
              password:$("input[name='password']").val(),
              user:$("#user")[0].checked,
              admin:$("#admin")[0].checked,
              beUser:$("#beUser").text()
            }

            $.post(
              'visitors.class.php',
              datas,
              function(data){
                var err = data ? $.parseJSON(data):data;
                if(err.error){
                  $('#error_password').css("display" , "inline").text( err.error.password ? err.error.password : "");
                  $('#error_radio').css("display" , "inline").text( err.error.radio ? err.error.radio : "");
                }else if(err == true){
                  window.location.href="http://localhost/Vilog/visitors.php";
                }  
              }
            )
        });
      });
    </script>

  </body>
</html>
