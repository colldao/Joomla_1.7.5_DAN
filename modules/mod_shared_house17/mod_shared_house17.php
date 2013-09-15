<?php
/**
 * Hello World! Module Entry Point
 *
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @link http://dev.joomla.org/component/option,com_jd-wiki/Itemid,31/id,tutorials:modules/
 * @license        GNU/GPL, see LICENSE.php
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<style>
a
{
text-decoration:none;
background:none ;
}
a:hover
{
text-decoration:underline;
background:none ;
}
table
{
width:200px !important;
}
</style>
<?php
// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

//$hello = modHelloWorldHelper::getHello( $params );
//require( JModuleHelper::getLayoutPath( 'mod_helloworld' ) );
// include the template for display
require(JModuleHelper::getLayoutPath('mod_shared_house17'));


/**
 * @version 1.0 $
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/** ensure this file is being included by a parent file */
//defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

$numero = intval( $params->get( 'numero', 5 ) );
$langue = $params->get( 'langue') ;
$saisons = $params->get( 'saisons') ;
$link=$params->get( 'articles') ;
 
$showlink=$params->get( 'showlink') ;
if(!($link>0))
{
 
$showlink=0;
}
else
$link="index.php?option=com_content&view=article&id=".$link;
// Déclaration
$lien_signature['fr']='http://www.shared-house.com/index_booking_calendar.php';
$lien_signature['en']='http://www.shared-house.com/free-booking-calendar.php';
$lien_signature['nl']='http://www.shared-house.com/index_booking_calendar_nl.php?&langue=nl';
$lien_signature['es']='http://www.shared-house.com/index_booking_calendar_es.php?&langue=es';
$lien_signature['fi']='http://www.shared-house.com/free-booking-calendar.php';
$lien_signature['de']='http://www.shared-house.com/index_booking_calendar_de.php?&langue=de';


$lien_accueil_c['fr']="http://www.shared-house.com";
$lien_accueil_c['en']="http://www.shared-house.com/index.php?&langue=en";
$lien_accueil_c['nl']="http://www.shared-house.com/index.php?&langue=nl";
$lien_accueil_c['es']="http://www.shared-house.com/index.php?&langue=es";
$lien_accueil_c['fi']="http://www.shared-house.com/index.php?&langue=fi";
$lien_accueil_c['de']="http://www.shared-house.com/index.php?&langue=de";

$text['toutes_dispos']['fr']='Toutes les disponibilites - reservation ou demande d\'information';$text['toutes_dispos']['en']='Availabilities overview - Reservation and information request';$text['toutes_dispos']['nl']='Overzicht van de beschikbaarheid - reservering of informatie aanvragen';$text['toutes_dispos']['fi']='Tarkista saatavuus - varaukset ja varauspyynnot';
$text['toutes_dispos']['es']='Todas las disponibilidades - reserva o solicitud de informacion';
$text['toutes_dispos']['de']='übersicht der Verfügbarkeit, Buchungs- und Informationsanfrage';


$digit=$numero-10*(intval($numero/10));

$text['catch1.1']['fr']=' pour votre ';$text['catch1.1']['en']=' for your ';$text['catch1.1']['nl']=' voor uw ';$text['catch1.1']['fi']=' sinun ';		$text['catch1.1']['de']=' für Ihre '; 		$text['catch1.1']['es']=' para su ';
 
switch ($digit) {
	case 0:
	$text['catch1']['es']='reserva natural';
		$text['catch1']['fr']='calendrier reservation';$text['catch1']['en']='free booking calendar';$text['catch1']['nl']='gratis reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier disponibilite';$text['catch2']['en']='free availability calendar';$text['catch2']['nl']='gratis beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';$text['catch2']['es']='calendario de disponibilidad';
		$text['catch1.3']['fr']='annonce location gratuite';$text['catch1.3']['en']='free holiday advertising';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';

		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
                  $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 1:
	$text['catch1']['es']='Calendario de disponibilidad';
		$text['catch1']['fr']='calendrier de reservation';$text['catch1']['en']='booking calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch2']['fi']='calendario de libre disponibilidad';
		$text['catch1.3']['fr']='location saisonniere vacances';$text['catch1.3']['en']='free online ads';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';
		 
		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
		$text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 2:
	$text['catch1']['es']='Alquiler de Calendario';
		$text['catch1']['fr']='calendrier de location';$text['catch1']['en']='online booking calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='online availability calendar';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch2']['es']='calendario de libre disponibilidad';
		$text['catch1.3']['fr']='annonce gratuite location';$text['catch1.3']['en']='cottage holidays';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';
	
		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
	        	$text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 3:
 
	$text['catch1']['es']='la planificación de reservas';
		$text['catch1']['fr']='planning de reservation';$text['catch1']['en']='joomla booking calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendars';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch1.3']['fr']='annonce gratuite de location';$text['catch1.3']['en']='cottages to rent';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';
	
		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
               $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 4:
	$text['catch1']['es']='reservas horario';
		$text['catch1']['fr']='calendrier reservations';$text['catch1']['en']='reservation calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar free';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch1.3']['fr']='location vacances saisonniere';$text['catch1.3']['en']='holiday accommodation';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';

		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
                   $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 5:
	$text['catch1']['es']='reserva guión calendario';
		$text['catch1']['fr']='script calendrier reservation';$text['catch1']['en']='php booking calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar for website';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch1.3']['fr']='location villa vacances';$text['catch1.3']['en']='cottage holiday rental';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';

		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
	           $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 6:
	$text['catch1']['es']='calendario de reservas gratuito';
		$text['catch1']['fr']='calendrier reservation php';$text['catch1']['en']='web booking calendar';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar script';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch1.3']['fr']='location vacances particulier';$text['catch1.3']['en']='holiday home rentals';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';

		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
	          $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 7:
	$text['catch1']['es']='Calendario de disponibilidad';
		$text['catch1']['fr']='calendrier de reservation';$text['catch1']['en']='booking calendar free';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar html';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch1.3']['fr']='location gite vacances';$text['catch1.3']['en']='holiday rental france';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';
	
		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['es']='Gratis Verfügbarkeitskalender';
	
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 8:
	$text['catch1']['es']='reserva natural';
		$text['catch1']['fr']='calendrier reservation';$text['catch1']['en']='booking calendar script';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='availability calendar widget';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='calendario de libre disponibilidad';
		   $text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['fr']='location appartement vacances';$text['catch1.3']['en']='holiday villa rental';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';
	
		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['es']='Gratis Verfügbarkeitskalender';

		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;
	case 9:
	 
	$text['catch1']['es']='la planificación de reservas';
		$text['catch1']['fr']='planning reservation';$text['catch1']['en']='booking calendar html';$text['catch1']['nl']='reserveringskalender';$text['catch1']['fi']='ilmainen varauskalenteri';
		$text['catch2']['fr']='calendrier de disponibilite';$text['catch2']['en']='joomla availability calendar';$text['catch2']['nl']='beschikbaarheidskalender';$text['catch2']['fi']='ilmainen saatavuuskalenteri';
		$text['catch2']['es']='calendario de libre disponibilidad';
		$text['catch1.3']['fr']='location maison vacances';$text['catch1.3']['en']='holiday villa rentals';$text['catch1.3']['nl']='vakantiewoning verhuur';$text['catch1.3']['fi']='lomavuokraus';

		$text['catch1.3']['es']='anuncio de alquiler';
		$text['catch1']['de']='Gratis Buchungskalender';
		$text['catch2']['de']='Gratis Verfügbarkeitskalender';
$text['catch2']['es']='libre Verfügbarkeitskalender';
		$text['catch1.3']['de']='Ferienwohnung Anzeige';
		break;

}


// echo JText::_('ADNUMBER');

if ($saisons=='avec')
{


	// 200
	// iframe 211
	//  border-style: none; border-width: 0px;
	// style="margin-left: -19px;
	// 196 / 190
	?>

<table align="center"
	style="text-align: center; width: 198px; border-style: none; border-width: 0px; border-collapse: collapse; margin: 0 auto;">
	<!-- <tr><td></td></tr> 	-->
	<tr align="center">
		<td>
		<div
			style="margin-left: -33px; border-style: none; border-width: 0px;"><iframe
			border-style:none;	border-width: 0px; src="http://www.shared-house.com/calendar_iframe_1.php?numero=<?php echo $numero; ?>&langue=<?php echo $langue; ?>&style=1"
			scrolling="no" frameborder="0" height="150" width="192" title=""
			allowtransparency="true";></iframe></div>
		</td>
	</tr>

	<tr>

		<td
			style="border-style: none; border-width: 0px; text-transform: uppercase; line-height: 1; vertical-align: top;">
		<div
			style="margin-left: -4px; border-style: none; border-width: 0px; text-align: center;">
		<?php  if(!$showlink){ ?><a target="_top"
			style="font-family: Verdana; font-size: 10px; color: #006600; text-align: center; font-weight: bold;"
			href="http://www.shared-house.com/vacancy_planning.php?numero=<?php echo $numero; ?>&langue=<?php echo $langue; ?>&style=1"><?php echo $text['toutes_dispos'][$langue] ; ?></a>
			 <?php } else {   ?>
		<a target="_top"
			style="font-family: Verdana; font-size: 10px; color: #006600; text-align: center; font-weight: bold;"
			href="<?php echo $link ?> "><?php echo $text['toutes_dispos'][$langue] ; ?></a>
			<?php } ?>
			</div>
		</td>
	</tr>

	<tr>
		<td>
		<div style="margin-left: -5px; border-style: none; border-width: 0px;">
		<?php
		if ($langue=='es' or $langue=='es')
		{
			$height_haut=127;
		}
		else
		$height_haut=117;
		?> 
		 <iframe
			src="http://www.shared-house.com/cal_legend_iframe_1.php?langue=<?php echo $langue; ?>"
			scrolling="no" frameborder="0" height="<?php echo $height_haut; ?>"
			width="198" title="" allowtransparency="true";></iframe></div>
		</td>
	</tr>

	<tr> 
	<?php
	echo '<td style="line-height: 1; vertical-align: top;"><a
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"
			href="'.$lien_signature[$langue].'">'.$text['catch1'][$langue].'</a><span
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"><br>
		'.$text['catch1.1'][$langue].'</span><a
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"
			href="'.$lien_accueil_c[$langue].'">'.$text['catch1.3'][$langue].'</a></td>';
	?>
	</tr>
	<!-- 
-->
</table>

	<?php

 
}
else
{ // sans saisons
 

?>
<table align="center"
	style="text-align: center; width: 196px; border-style: none; border-width: 0px; border-collapse: collapse; margin: 0 auto;">
	<!-- <tr><td></td></tr> 	-->
	<tr align="center">
		<td>
		<div
			style="margin-left: -33px; border-style: none; border-width: 0px;"><iframe
			border-style:none;	border-width: 0px; src="http://www.shared-house.com/calendar_iframe_1.php?numero=<?php echo $numero; ?>&langue=<?php echo $langue; ?>&style=1"
			scrolling="no" frameborder="0" height="150" width="192" title=""
			allowtransparency="true";></iframe></div>
		</td>
	</tr>

	<tr>

		<td
			style="border-style: none; border-width: 0px; text-transform: uppercase; line-height: 1; vertical-align: top;">
		<div
			style="margin-left: -4px; border-style: none; border-width: 0px; text-align: center;">
<?php  if(!$showlink){?>		
<a target="_top"
			style="font-family: Verdana; font-size: 10px; color: #006600; text-align: center; font-weight: bold;"
			href="http://www.shared-house.com/vacancy_planning.php?numero=<?php echo $numero; ?>&langue=<?php echo $langue; ?>&style=1"><?php echo $text['toutes_dispos'][$langue]; ?></a>
<?php }else
{ ?>
<a target="_top"
			style="font-family: Verdana; font-size: 10px; color: #006600; text-align: center; font-weight: bold;"
			href="<?php echo $link ?> "><?php echo $text['toutes_dispos'][$langue]; ?></a>


<?php } ?>
</div>
		</td>
	</tr>



	<tr>
		<td>
		<div style="margin-left: -5px; border-style: none; border-width: 0px;">
		<iframe
			src="http://www.shared-house.com/cal_legend_iframe_simple_1.php?langue=<?php echo $langue; ?>"
			scrolling="no" frameborder="0" height="60" width="198" title=""
			allowtransparency="true";></iframe></div>
		</td>
	</tr>

	<tr>

		<td style="line-height: 1; vertical-align: top;"><?php
		echo '<a
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"
			href="'.$lien_signature[$langue].'">'.$text['catch2'][$langue].'</a><span
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"><br>
		'.$text['catch1.1'][$langue].'</span><a
			style="font-family: Verdana; font-size: 9px; text-align: center; font-weight: normal; color: #006600;"
			href="'.$lien_accueil_c[$langue].'">'.$text['catch1.3'][$langue].'</a>';
		?></td>
	</tr>
</table>
		<?php

}

?>



