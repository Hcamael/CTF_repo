<?php
include("config.php");
$user = new User();
if(!$user->is_loaded()){
	header('Location: login.php' );
	exit();
}

$manager = new MessageManager();

$messages = $manager->all($user->get_property('username'));

if(!$messages) die('No unread message');
?>

Unread Messages
<table>

<thead>

<tr>

  <th>from</th>

  <th>message</th>

  <th>read</th>

</tr>

</thead>

<tbody>

<?php foreach($messages as $message){ ?>

<tr>

<td><?php echo htmlspecialchars($message->from); ?></td>


<td><?php echo htmlspecialchars($message->msg); ?></td>

<td><a href="show.php?id=<?php echo $message->id; ?>">click here</a></td>


</tr>

<?php } ?>

</tbody>

</table>