<?php
namespace dologin;
defined( 'WPINC' ) || exit;

?>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">

<div class="d-flex justify-content-center mt-5">
<div class="alert alert-primary alert-dismissible shadow">
	<h4 class="alert-heading"><?php echo __( 'Notice', 'dologin' ); ?></h4>

	<p class="mt-3"><?php echo __( 'You will login as the following user', 'dologin' ); ?>: </p>

	<p class="h5 mb-3 ml-3 text-success"><?php echo $username; ?></p>

<?php if ( $row->onetime ) : ?>
<div class="alert alert-warning" role="alert">
	<?php echo __( 'Note: this is a one time usage link.', 'dologin' ); ?>
</div>
<?php endif; ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" class="mt-5">
	<input type="hidden" name="confirmed" value="1">
	<button type="submit" class="btn btn-success btn-lg shadow"><?php echo __( 'Click here to login', 'dologin' ); ?></button>
</form>

</div>
</div>