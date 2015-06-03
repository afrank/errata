<? include('header.php'); ?>
<style>
a {
  text-decoration: none;
  color: #000;
}
thead.active {
  font-weight: 900;
  background-color: #eee;
}
td {
  padding: 10px;
}
</style>
<? $VIRT = build_virt_array($conn); ?>

<div class="row" style="margin-top:-30px;margin-left:10px;">
<div class="col-md-8" style="margin-top:30px;">
<h3>Active Environments</h3>
<table class="table-striped table-hover table-bordered" style='vertical-align:top;'>
<thead class="active">
	<tr>
		<td>Created On</td>
		<td>Owner</td>
		<td>Hosts</td>
		<td>Actions</td>
	</tr>
</thead>

<tbody>
	<? foreach($VIRT as $v) { ?>
		<tr>
			<td><?=$v['environment']->created_on?></td>
			<td><?=$v['environment']->owner?></td>
			<td>
				<? foreach($v['instances'] as $instance) { ?>
				<?=$instance->address?> (<?=$instance->type?>)<br/>
				<? } ?>
			</td>
			<? if($v['environment']->is_expired == 1) { ?>
				<td><a href="submit.php?unexpire=<?=$v['environment']->id?>"><button class="btn btn-warning">CANCEL EXPIRATION</button></a></td>
			<? } else { ?>
				<td><a href="submit.php?expire=<?=$v['environment']->id?>"><button class="btn btn-danger">EXPIRE</button></a></td>
			<? } ?>
		</tr>
	<?php } ?>
</tbody>
</table>
</div>
<div class="col-md-4">
<div class="bs-callout bs-callout-info">
<h4>Request an Environment</h4>

<form method="POST" action="post_request.php" role="form">
  <div class="form-group">
    <label for="email">Email address</label>
    <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
  </div>
  <div class="form-group">
    <label for="branch">Branch</label>
    <input type="text" class="form-control" name="branch" id="branch" placeholder="Branch (optional)">
  </div>
  <button type="submit" class="btn btn-success">Go!</button>
</form>
<? $QUEUE = build_request_queue($conn); ?>
<? if(sizeof($QUEUE) > 0) {?>
	<p>&nbsp;</p>
	<h4>Pending Requests</h4>
	<table class="table-striped table-hover table-bordered" style='vertical-align:top;'>
	<thead style="font-weight:900;">
		<tr>
		<td>Date</td>
		<td>Email Address</td>
		<!-- td>Branch</td -->
		<td>Status</td>
		</tr>
	</thead>
	<tbody>
	<? foreach($QUEUE as $q) { ?>
		<tr>
		<td><?=$q->requested_on?></td>
		<td><?=$q->email?></td>
		<!-- td><?=$q->branch?></td -->
		<td><?=$q->status?></td>
		</tr>
	<? } ?>
	</tbody>
	</table>
<? } ?>

</div>
</div>
      </div><!--/row-->
      <hr>
<? include('footer.php'); ?>
