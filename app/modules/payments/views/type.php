<style type="text/css">.bg-light-green {width: 100%;margin-bottom: 20px;}.pricing-table a {color: #59bc7e;}</style>
<div class="pricing-table">
	<?php if(!empty($package)){
		$discount = 0;
		$tolal = 0;
	?>
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<div class="invoice_table">
					<table class="table">
						<thead>
							<tr>
								<th><?=l("Package namee")?></th>
								<th class="text-center" style="width: 30%;"><?=l("Pricee")?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="uc text-left"><a href="http://www.instaplus.com.br/painel/index.php/payments"><?=$package->name?> - Voltar</a></td>
								<td class="text-center"><?=number_format($package->price,2)?></td>
							</tr>
							<tr class="active" class="invoice_success col-green">
								<td class="text-right"><?=l("")?></td>
								<td class="text-center" style="font-size: 30px;color: #63c489;"><?=number_format($package->price,2)?></td>
							</tr>
							<?php if(!empty($coupon)){?>
							<tr class="invoice_warning">
								<td class="text-right"><?=l("Discount:")?></td>
								<td class="text-center">
								<?php 
								if($coupon->type==1){
									$discount = (float)$coupon->price;
								}else{
									$discount = ((float)$coupon->price/100)*(float)$package->price;
								}
								$discount = ($discount < 0)?0:$discount;
								$tolal =number_format($package->price-$discount,2)*100;
								echo $payment->symbol.number_format($discount,2);
								?>
								</td>
							</tr>
							<?php }?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-md-4">
				<form action="<?=cn('ajax_coupon')?>" data-redirect="<?=cn("type?package=").(int)get("package")?>">
					<div class="invoice_coupon">
						<div class="invoice_title title uc"><?=l("Promotional Code")?></div>
						<div class="invoice_content">
							<div class="form-group">
					     		<input type="hidden" class="form-control" name="package_id" value="<?=get("package")?>">
					     		<input type="text" class="form-control" name="coupon" value="<?=(!empty($coupon))?$coupon->code:""?>" placeholder="Se tiver um cupom, insira aqui..." style="border: 2px solid #dddddd;">
					     	</div>
					     	<button type="button" class="btn btn-block bg-blue btn-lg btn-light-green btnActionUpdate" style="margin-bottom: 20px; font-size: 16px;font-weight: bold;"><?=l("Validate Code")?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php }?>

	<div class="container">
		<div class="row">
			<?php if($payment->paypal_email !=""){?>
			<div class="col-md-4">
				<div class="whole" style="width: 100%; margin: 0 0 15px;">
					<div class="plan" style="width: 100%;">
						<div class="header">
							<img src="<?=BASE."assets/images/paypal_logo.png"?>" height="100">
						</div>
						<div class="price">
							<?php if(session("uid")){?>
				      			<a href="<?=cn("do_payment?package=".get("package"))?>" class="btn btn-block bg-light-green btn-lg waves-effect"><?=l('PAYMENT NOW')?></a>
							<?php }else{?>
								<a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-block bg-light-green btn-lg waves-effect"><?=l('PAYMENT NOW')?></a>
				      		<?php }?>
						</div>
					</div>
				</div>
			</div>
			<?php }?>

			
			<?php if($payment->stripe_email !=""){?>
			<div class="col-md-4">
				<div class="whole" style="width: 100%; margin: 0 0 15px;">
					<div class="plan" style="width: 100%;">
						<div class="header">
							<img src="<?=BASE."assets/images/cards.png"?>" height="100">
						</div>
						<div class="price">
							<?php if(session("uid")){?>
							<form action="<?=PATH."payments/stripe_process?package=".(int)get("package")?>" method="post">
				      			<a href="javascript:void(0);" class="btn btn-block bg-light-green btn-lg waves-effect btnStripePayment" data-key="<?=$payment->stripe_pk?>" data-amount="<?=$tolal?>" data-stripe-panel-label="<?=l("Pay")?> <?=$tolal?>" data-currency="<?=$payment->currency?>" data-name="<?=$package->name?> <?=l('Package')?>" data-url="<?=PATH."payments/stripe_process?package=".(int)get("package")?>" data-email="<?=$payment->stripe_email?>" data-stripe-img="<?=BASE?>assets/images/shield.png"><?=l('PAYMENT NOW')?></a>
				      		</form>
							<?php }else{?>
								<a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-block bg-light-green btn-lg waves-effect"><?=l('PAYMENT NOW')?></a>
				      		<?php }?>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
			<?php if($payment->pagseguro_email !=""){?>
			<div class="col-md-4">
				<div class="whole" style="width: 100%; margin: 0 0 15px;">
					<div class="plan" style="width: 100%;">
						<div class="header">
							<img src="<?=BASE."assets/images/pagseguro.gif"?>" height="100">
						</div>
						<div class="price">
							<?php if(session("uid")){?>
				      			<a href="<?=cn("do_payment_pagseguro?package=".get("package"))?>" class="btn btn-block bg-light-green btn-lg waves-effect"><?=l('PAYMENT NOW')?></a>
							<?php }else{?>
								<a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-block bg-light-green btn-lg waves-effect"><?=l('PAYMENT NOW')?></a>
				      		<?php }?>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
		</div>
	</div>
</div>
<?=modules::run("blocks/footer")?>
<script src="https://checkout.stripe.com/v2/checkout.js"></script>
<script type="text/javascript">
 	$(document).ready(function() {
        $('.btnStripePayment').on('click', function(event) {
            event.preventDefault();
            var $button = $(this),
                $form = $button.parents('form');
            var opts = $.extend({}, $button.data(), {
                token: function(result) {
                    $form.append($('<input>').attr({ type: 'hidden', name: 'stripeToken', value: result.id })).submit();
                }
            });
            StripeCheckout.open(opts);
        });
    });
</script>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
Stripe.setPublishableKey('pk_test_z64ycgNobgmDYYULv4Kvwkd4');
$(function() {
  	var $form = $('#payment-form');
  	$form.submit(function(event) {
		// Disable the submit button to prevent repeated clicks:
		$form.find('.submit').prop('disabled', true);
		$form.find('.submit').val('Please wait...');

		// Request a token from Stripe:
		Stripe.card.createToken($form, stripeResponseHandler);
		// Prevent the form from being submitted:
		return false;
	});
});

function stripeResponseHandler(status, response) {
 	if (response.error) {
		alert(response.error.message);
 	}else{
		$.ajax({
			url: PATH+"payments/stripe_process",
			data: {access_token: response.id},
			type: 'POST',
			dataType: 'JSON',
			success: function(response){
				console.log(response);
				if(response.success)
				window.location.href=PATH+"payments/stripe_success";
			},
			error: function(error){
				console.log(error);
			}
		});
	}
}
</script>
