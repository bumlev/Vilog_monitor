
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
    <link rel="stylesheet" type="text/css" href="./css/style.css" />
    <script src="//code.jquery.com/jquery-1.12.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <title>Vilog</title>
  </head>
  <body>
    <?php 
      include_once 'objet.php';
      include_once 'roles.class.php';
      $roles = new roles($db);
    ?>
    

    <div id="container" class="container">
        <div id="search" class="search">
            <form id="search_id" action="#" class="search_id">
              <input id="IdNumber_search" placeholder="Enter your ID Number ..." type="text">
              <span id="error_search_IdNumber" class="error_search"></span>
              <button id="search_button" name="search">Search</button>
              <button id="disconnect" name="disconnect">Disconnect</button>
            </form>
            <div id="action" class="action">
              <div class="action_login"><span>are you a user ?</span><a id="action_login" href="#">login</a></div>
              <div class="action_register"><span>new to Vilog ?</span><a id="action_register" href="#">register</a></div>
            </div>
        </div>
        <form action="#" id="signup" class="signup" method="post">
            <h1>Register</h1>
          <input style="display:none"  placeholder="Enter visitor ..." type="text" name="visitor">
          <input placeholder="Enter firstname ..." type="text" name="firstname">
          <span id="error_firstname" class="error"></span>

          <input placeholder="Enter lastname ..." type="text" name="lastname">
          <span id="error_lastname" class="error"></span>

          <input placeholder="Enter an email ..." type="email" name="email">
          <span id="error_email" class="error"></span>
          <?php 
            $roles->selectroles()
          ?>
          <input  placeholder="Enter ID Number ..." type="text" name="IdNumber">
          <span id="error_IdNumber" class="error"></span>

          <input  placeholder="Enter Phone Number ..." type="tel" name="phone">
          <span id="error_message" class="error_message">The User already exists !</span>
          
          <button name="register" id="register" class="register"> Register</button>
          <button  name="present" id="present" class="register"> Present</button>
        </form>

        <form action="#" id="signin" class="signin">
            <h1>Login</h1>
            <input placeholder="Enter an email ..." type="email" name="email_login">
            <input placeholder="Enter password ..." type="password" name="password_login">
            <button id="login" class="register"> Login</button>
        </form> 
        <div id="thank" class="search">
            <h1 style="color: vert">Thank you !</h1>
            <button id="back" name="search">Back</button>
        </div>
    </div>

    <script>
      $(document).ready(function(){
        $('#signin').hide();
        $('#signup').hide();
        $("#thank").hide();
        $("#disconnect").hide();
        $('#present').on('click' , function(e){
          e.preventDefault();
          var donnees = {
              visitor:$("input[name='visitor']").val(),
              firstname:$("input[name='firstname']").val(), 
              lastname:$("input[name='lastname']").val(), 
              email:$("input[name='email']").val(), 
              role:$("#role").val(),
              IdNumber:$("input[name='IdNumber']").val(),
              phone:$("input[name='phone']").val(),
              present:$("#present").text()
          };
          $.post(
            'visitors.class.php',
            donnees,
            function(data){
              $('#signup').hide(800);
              $('#thank').show({direction : 'right' } , 900);
            }
          )

        });

        $('#search_id').on('submit' , function(e){
          e.preventDefault();
          var donnees = {IdNumber : $('#IdNumber_search').val() , search: $('#search_button').text()};
          $.post(
            'visitors.class.php',
            donnees,
            function(data){
              var data = data ? $.parseJSON(data):data;
              if(data.error)
                $("#error_search_IdNumber").css("display" , "inline").text( data.error.IdNumber ? data.error.IdNumber : "")
              else if(data === false)
                $("#error_search_IdNumber").css("display" , "inline").text( "You are not found ...");
              else if(data.connected == 1){
                $("#IdNumber_search").hide(900);
                $("#search_button").hide(900);
                $('#error_search_IdNumber').text("");
                $("input[name='visitor']").val(data[0]);
                $("#disconnect").show({direction : 'right' } , 900);
              }
              else{
                $("input[name='visitor']").val(data[0]);
                $("input[name='firstname']").val(data.firstname); 
                $("input[name='lastname']").val(data.lastname); 
                $("input[name='email']").val(data.email);
                $("#role").val(data.roleId).change();
                $("input[name='IdNumber']").val(data.IdNumber); 
                $("input[name='phone']").val(data.PhoneNumber);
                $('#search').hide(800);
                $("#register").css('display' , 'none');
                $("#present").css('display' , 'inline');
                $('#signup').show({direction : 'right' } , 900 );
              }
            }
          )
        });



        $('#register').on('click' , function(e){
            e.preventDefault();
            var donnees = {
              firstname:$("input[name='firstname']").val(), 
              lastname:$("input[name='lastname']").val(), 
              email:$("input[name='email']").val(), 
              role:$("#role").val(),
              IdNumber:$("input[name='IdNumber']").val(),
              phone:$("input[name='phone']").val(),
              register:$("#register").text()
            };

            $.post(
              'visitors.class.php',
              donnees,
              function(data){
                var err = data ? $.parseJSON(data):data;
                if(err.error){
                  $('#error_firstname').css("display" , "inline").text( err.error.firstname ? err.error.firstname : "");
                  $('#error_lastname').css("display" , "inline").text( err.error.lastname ? err.error.lastname : "");
                  $('#error_email').css("display" , "inline").text( err.error.email ? err.error.email : "");
                  $('#error_role').css("display" , "inline").text( err.error.role ? err.error.role : "");
                  $("#error_IdNumber").css("display" , "inline").text( err.error.IdNumber ? err.error.IdNumber : "")
                }else if(err == true){
                    $("input, textarea , select ").val("");
                    $("span ").text("");
                    $('#signup').hide(800);
                    $('#signin').show({direction : 'right' } , 900);
                }else if(err === false){
                    $('.error_message').css("display" , "inline").text("User exists already !");
                }
              }
            )
        })


        $('#login').on('click' , function(e){
            e.preventDefault();
            $("input, textarea , select ").val("");
            $('#signin').hide(800);
            $('#signup').show('slide' , {direction : 'left' } ,900);
        })


        $('#action_register').on('click' , function(e){
          $("input, textarea , select ").val("");
          $('#search').hide(800);
          $('#signup').show({direction : 'right' } , 900 );
        })

        $('#action_login').on('click' , function(e){
          $('#search').hide(800);
          $('#signin').show({direction : 'right' } , 900 );
        })

        $('#back').on('click' , function(e){
          e.preventDefault();
          $("input, textarea , select ").val("");
          $('#thank').hide(800);
          $('#search').show({direction : 'left' } , 900 );
        })

        $("#disconnect").on('click' , function(e){
          e.preventDefault();
          var donnees = {visitor: $("input[name='visitor']").val() , disconnect: $("#disconnect").text()};
          $.post(
            'visitors.class.php',
            donnees,
            function(data){
                $("#disconnect").hide(900);
                $('input').val("")
                $('#error_search_IdNumber').text("");
                $("#IdNumber_search").show({direction : 'right' } , 900);
                $("#search_button").show({direction : 'right' } , 900);
            }
          )
        })
      })

    </script>

  </body>
</html>
