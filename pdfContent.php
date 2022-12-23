<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="Automated System Monitor "/>
  
    <title>Pdf</title>
  </head>
  <body>
    <style>
        table{
            border-collapse: collapse;
            width:100%;
        }

        td, th{
            border:1px solid black;
            padding: 4px;
            font-size:13px;
        }
    </style>
        <h1>List of Visitors</h1>
        <table class="table">
            <thead>
                <th>Firstname</th>
                <th>Lastname</th>
                <th>Role</th>
                <th>Email</th>
                <th>IdNumber</th>
                <th>Come in</th>
                <th>Come out</th>
            </thead>
            <tbody>
                <?php  foreach($visitors as $visitor): ?>
                    <tr>
                        <td><?=$visitor["firstname"] ?></td>
                        <td><?=$visitor["lastname"] ?></td>
                        <td><?=$visitor["name"] ?></td>
                        <td><?=$visitor["email"] ?></td>
                        <td><?=$visitor["IdNumber"] ?></td>
                        <td><?=$visitor["created_at"] ?></td>
                        <td><?=$visitor["updated_at"] == null ? "pending ...": $visitor["updated_at"]?></td>
                    </tr> 
                <?php endforeach; ?>
            </tbody>
        </table>
  </body>
</html>