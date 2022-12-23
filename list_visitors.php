<?php 
           include_once 'objet.php';
           include_once 'visitors.class.php';
        ?>
<table id="table" class="table">
    <thead>
        <tr>
        <th scope="col">Firstname</th>
        <th scope="col">Lastname</th>
        <th scope="col">Role</th>
        <th scope="col">Email</th>
        <th scope="col">IdNumber</th>
        <th scope="col">PhoneNumber</th>
        <th scope="col">Time in</th>
        <th scope="col">Time out</th>
        </tr>
    </thead>
        <?php 
        $visitors->list_visitors();
        $visitors->list_employees();
        ?>
</table>