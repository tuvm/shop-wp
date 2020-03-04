<?php
/**
 * PDF invoice header template that will be visible on every page.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/invoice/simple/yourtemplatename/header.php.
 *
 * HOWEVER, on occasion WooCommerce PDF Invoices will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Bas Elbers
 * @package WooCommerce_PDF_Invoices/Templates
 * @version 0.0.1
 */

$templater       = WPI()->templater();
$order           = $templater->order;
$invoice         = $templater->invoice;
?>

<table cellpadding="0" cellspacing="0">
	<tr class="top">
		<td>
			<?php
			if ( $templater->get_logo_url() ) {
				printf( '<img src="var:company_logo" style="max-height:100px;"/>' );
			} else {
				printf( '<h2>%s</h2>', esc_html( $templater->get_option( 'bewpi_company_name' ) ) );
			}
			?>
		</td>

		<td>
			<?php
			/**
			 * Invoice object.
			 *
			 * @var BEWPI_Invoice $invoice.
			 */
			foreach ( $invoice->get_invoice_info() as $id => $info ) {
				printf( '<span class="%1$s">%2$s %3$s</span>', esc_attr( $id ), esc_html( $info['title'] ), esc_html( $info['value'] ) );
				echo '<br>';
			}
			?>
		</td>
	</tr>
</table>
