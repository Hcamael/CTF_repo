<?php
include("config.php");
$user = new User();
if(!$user->is_loaded()){
	header('Location: login.php' );
	exit();
}

$manager = new MessageManager();

$message = $manager->one($user->get_property('username'), $_GET['id']);

if(!$message){die("No message");}
$manager->read($message->id);

?>

<table>

<thead>

<tr>

  <th>from</th>

  <th>message</th>

</tr>

</thead>

<tbody>


<tr>

<td><?php echo $message->from; ?></td>


<td><?php echo $message->msg; ?></td>


</tr>


</tbody>

</table>