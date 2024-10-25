<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2021 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2010-2012 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2012      Christophe Battarel  <christophe.battarel@altairis.fr>
 * Copyright (C) 2014      Ion Agorria          <ion@agorria.com>
 * Copyright (C) 2015      Alexandre Spangaro   <aspangaro@open-dsi.fr>
 * Copyright (C) 2016      Ferran Marcet		<fmarcet@2byte.es>
 * Copyright (C) 2019      Frédéric France      <frederic.france@netlogic.fr>
 * Copyright (C) 2019      Tim Otte			    <otte@meuser.it>
 * Copyright (C) 2020      Pierre Ardoin        <mapiolca@me.com>
 * Copyright (C) 2023	   Joachim Kueter		<git-jk@bloxera.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/product/fournisseurs.php
 *  \ingroup    product
 *  \brief      Page of tab suppliers for products
 */

// Load Dolibarr environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_expression.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';

// Net Logic
include_once './Productrefbycustomer.class.php';

if (isModEnabled('barcode')) {
	dol_include_once('/core/class/html.formbarcode.class.php');
}

global $langs;

// Load translation files required by the page
$langs->loadLangs(['main', 'dict', 'bills', 'products', 'companies', 'propal', 'orders', 'contracts', 'interventions', 'deliveries', 'sendings', 'projects', 'productbatch', 'infraspackplus@infraspackplus', 'refbycustomer@refbycustomer']);

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$rowid = GETPOST('rowid', 'int');
$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'alpha');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'pricesuppliercard';

$socid = GETPOST('socid', 'int');
$cost_price = price2num(GETPOST('cost_price', 'alpha'), '', 2);
$pmp = price2num(GETPOST('pmp', 'alpha'), '', 2);

$backtopage = GETPOST('backtopage', 'alpha');
$error = 0;

$extrafields = new ExtraFields($db);

// If socid provided by ajax company selector
if (GETPOST('search_fourn_id', 'int')) {
	$_GET['id_fourn'] = GETPOST('search_fourn_id', 'int');
	$_POST['id_fourn'] = GETPOST('search_fourn_id', 'int');
}

// Security check
$fieldvalue = (!empty($id) ? $id : (!empty($ref) ? $ref : ''));
$fieldtype = (!empty($ref) ? 'ref' : 'rowid');
if ($user->socid) {
	$socid = $user->socid;
}

if (empty($user->rights->fournisseur->lire) && (!isModEnabled('margin') && !$user->hasRight("margin", "liretous"))) {
	accessforbidden();
}

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = (GETPOST("page", 'int') ?GETPOST("page", 'int') : 0);
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
	$sortfield = "s.nom";
}
if (!$sortorder) {
	$sortorder = "ASC";
}

// $object = new ProductFournisseur($db);
$object = new Product($db);
if ($id > 0 || $ref) {
	$object->fetch($id, $ref);
}

$usercanread = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->lire) || ($object->type == Product::TYPE_SERVICE && $user->hasRight('service', 'lire')));
$usercancreate = (($object->type == Product::TYPE_PRODUCT && $user->rights->produit->creer) || ($object->type == Product::TYPE_SERVICE && $user->hasRight('service', 'creer')));

if ($object->id > 0) {
	if ($object->type == $object::TYPE_PRODUCT) {
		restrictedArea($user, 'produit', $object->id, 'product&product', '', '');
	}
	if ($object->type == $object::TYPE_SERVICE) {
		restrictedArea($user, 'service', $object->id, 'product&product', '', '');
	}
} else {
	restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype);
}

/*
 * Actions
 */

if ($cancel) {
	$action = '';
}

$parameters = array('socid'=>$socid, 'id_prod'=>$id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

$prodcustref = new Productrefbycustomer($db);

if ($action == 'save_price') {
	$id_fourn = GETPOST("id_fourn");
	if (empty($id_fourn)) {
		$id_fourn = GETPOST("search_id_fourn");
	}
	// add price by customer
	$prodcustref->fk_soc = GETPOST('id_fourn', 'int');
	$prodcustref->ref_customer_prd = GETPOST('ref_fourn', 'alpha');
	$prodcustref->fk_product = $object->id;
	if ($id_fourn <= 0) {
		$error++;
		$langs->load("errors");
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Supplier")), null, 'errors');
	}

	if (!$error) {
		$db->begin();

		if (!$error) {
			$ret = $prodcustref->create($user);
			if ($ret == -3) {
				$error++;
				setEventMessages($texttoshow, null, 'errors');
			} elseif ($ret < 0) {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		if (!$error) {
			$db->commit();
			$action = '';
		} else {
			$db->rollback();
		}
	} else {
		$action = 'create_price';
	}
}

if (empty($reshook)) {
	if ($action == 'add_customer_price_confirm' && !$cancel && ($user->hasRight('produit', 'creer') || $user->hasRight('service', 'creer'))) {
		$maxpricesupplier = $object->min_recommended_price();

		$update_child_soc = GETPOST('updatechildprice', 'int');

		// add price by customer
		$prodcustref->fk_soc = GETPOST('socid', 'int');
		$prodcustref->ref_customer_prd = GETPOST('ref_customer', 'alpha');
		$prodcustref->fk_product = $object->id;

		if (!($prodcustref->fk_soc > 0)) {
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ThirdParty")), null, 'errors');
			$error++;
			$action = 'add_ref_price';
		}

		if (!$error) {
			$result = $prodcustref->create($user, 0, $update_child_soc);

			if ($result < 0) {
				setEventMessages($prodcustref->error, $prodcustref->errors, 'errors');
			} else {
				setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			}

			$action = '';
		}
	}

	if ($action == 'delete_customer_price' && ($user->rights->produit->supprimer || $user->rights->service->supprimer)) {
		$prodcustref->fetch((int) $_GET['rowid']);
		$result = $prodcustref->delete($user);

		if ($result < 0) {
			setEventMessages($prodcustref->error, $prodcustref->errors, 'errors');
		} else {
			setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
		}
		$action = '';
	}

	if ($action == 'update_customer_price_confirm' && !$cancel && ($user->hasRight('produit', 'creer') || $user->hasRight('service', 'creer'))) {
		$update_child_soc = GETPOST('updatechildprice', 'int');

		$prodcustref->fetch((int) $_GET['prdid']);
		$prodcustref->ref_customer_prd = GETPOST('ref_fourn');
		$result = $prodcustref->update($user, 0, $update_child_soc);

		if ($result < 0) {
			setEventMessages($prodcustref->error, $prodcustref->errors, 'errors');
		} else {
			setEventMessages($langs->trans("Save"), null, 'mesgs');
		}

		$action = '';
	}
}

/*
 * view
 */

$form = new Form($db);

$title = $langs->trans('ProductServiceCard');
$helpurl = '';
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT)) {
	$title = $langs->trans('Product')." ".$shortlabel." - ".$langs->trans('BuyingPrices');
	$helpurl = 'EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos|DE:Modul_Produkte';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE)) {
	$title = $langs->trans('Service')." ".$shortlabel." - ".$langs->trans('BuyingPrices');
	$helpurl = 'EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios|DE:Modul_Lesitungen';
}

llxHeader('', $title, $helpurl, '', 0, 0, '', '', '', 'classforhorizontalscrolloftabs');

if ($id > 0 || $ref) {
	if ($result) {
		if ($action != 'edit' && $action != 're-edit') {
			$head = product_prepare_head($object);
			$titre = $langs->trans("CardProduct".$object->type);
			$picto = ($object->type == Product::TYPE_SERVICE ? 'service' : 'product');

			print dol_get_fiche_head($head, 'productref', $titre, -1, $picto);

			$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
			$object->next_prev_filter = "fk_product_type = ".((int) $object->type);

			$shownav = 1;
			if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
				$shownav = 0;
			}

			dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

			print '<div class="fichecenter">';

			print '<div class="underbanner clearboth"></div>';
			print '<table class="border tableforfield centpercent">';

			// Type
			if (isModEnabled("product") && isModEnabled("service")) {
				$typeformat = 'select;0:'.$langs->trans("Product").',1:'.$langs->trans("Service");
				print '<tr><td class="">';
				print (empty($conf->global->PRODUCT_DENY_CHANGE_PRODUCT_TYPE)) ? $form->editfieldkey("Type", 'fk_product_type', $object->type, $object, 0, $typeformat) : $langs->trans('Type');
				print '</td><td>';
				print $form->editfieldval("Type", 'fk_product_type', $object->type, $object, 0, $typeformat);
				print '</td></tr>';
			}

			print '</table>';

			print '</div>';
			print '<div class="clearboth"></div>';

			print dol_get_fiche_end();

			// Form to add or update a ref
			if (($action == 'create_price' || $action == 'update_price') && $usercancreate) {
				$langs->load("suppliers");

				print "<!-- form to add a supplier ref -->\n";
				print '<br>';
				if ($rowid) {
					$prodcustref->fetch($rowid, 1); //Ignore the math expression when getting the ref
					print load_fiche_titre('Modifier référence produit');
					print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=update_customer_price_confirm&prdid='.$prodcustref->id.'" method="POST">';
				} else {
					print load_fiche_titre('Créer référence produit');
					print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" method="POST">';
				}

				print '<form action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'" method="POST">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="action" value="save_price">';

				print dol_get_fiche_head();

				print '<table class="border centpercent">';

				// Supplier
				print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Societe").'</td><td>';
				if ($socid) {
					$supplier = new Societe($db);
					$supplier->fetch($socid);
					print $supplier->getNomUrl(1);
					print '<input type="hidden" name="id_fourn" value="'.$socid.'">';
					print '<input type="hidden" name="ref_fourn_price_id" value="'.$rowid.'">';
					print '<input type="hidden" name="rowid" value="'.$rowid.'">';
					print '<input type="hidden" name="socid" value="'.$socid.'">';
				} else {
					$events = array();
					$events[] = array('method' => 'getVatRates', 'url' => dol_buildpath('/core/ajax/vatrates.php', 1), 'htmlname' => 'tva_tx', 'params' => array());
					$filter = '';
					print img_picto('', 'company', 'class="pictofixedwidth"').$form->select_company(GETPOST("id_fourn", 'alpha'), 'id_fourn', $filter, 'SelectThirdParty', 0, 0, $events);

					$parameters = array('filtre'=>"", 'html_name'=>'id_fourn', 'selected'=>GETPOST("id_fourn"), 'showempty'=>1, 'prod_id'=>$object->id);
					$reshook = $hookmanager->executeHooks('formCreateThirdpartyOptions', $parameters, $object, $action);
					if (empty($reshook)) {
						if (empty($form->result)) {
							print '<a href="'.DOL_URL_ROOT.'/societe/card.php?action=create&type=f&backtopage='.urlencode($_SERVER["PHP_SELF"].'?id='.((int) $object->id).'&action='.urlencode($action).($action == 'create_price' ? '&token='.newToken() : '')).'">';
							print img_picto($langs->trans("CreateDolibarrThirdPartySupplier"), 'add', 'class="marginleftonly"');
							print '</a>';
						}
					}
					print '<script type="text/javascript">
					$(document).ready(function () {
						$("#search_id_fourn").change(load_vat)
						console.log("Requesting default VAT rate for the supplier...")
					});
					function load_vat() {
						// get soc id
						let socid = $("#id_fourn")[0].value

						// load available VAT rates
						let vat_url = "'.dol_buildpath('/core/ajax/vatrates.php', 1).'"
						//Make GET request with params
						let options = "";
						options += "id=" + socid
						options += "&htmlname=tva_tx"
						options += "&action=default" // not defined in vatrates.php, default behavior.

						var get = $.getJSON(
							vat_url,
							options,
							(data) => {
								rate_options = $.parseHTML(data.value)
								rate_options.forEach(opt => {
									if (opt.selected) {
										replaceVATWithSupplierValue(opt.value);
										return;
									}
								})
							}
						);
					}
					function replaceVATWithSupplierValue(vat_rate) {
						console.log("Default VAT rate for the supplier: " + vat_rate + "%")
						$("[name=\'tva_tx\']")[0].value = vat_rate;
					}
				</script>';
				}
				print '</td></tr>';

				// Ref supplier
				print '<tr><td class="fieldrequired">'.$langs->trans("SocieteRef").'</td><td>';
				if ($rowid) {
					print '<input type="hidden" name="ref_fourn_old" value="'.$prodcustref->ref_customer_prd.'">';
					print '<input class="flat width150" maxlength="128" name="ref_fourn" value="'.$prodcustref->ref_customer_prd.'">';
				} else {
					print '<input class="flat width150" maxlength="128" name="ref_fourn" value="'.(GETPOST("ref_fourn") ? GETPOST("ref_fourn") : '').'">';
				}
				print '</td>';
				print '</tr>';

				// Product description of the supplier
				if (!empty($conf->global->PRODUIT_FOURN_TEXTS)) {
					//WYSIWYG Editor
					require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

					print '<tr>';
					print '<td>'.$langs->trans('ProductSupplierDescription').'</td>';
					print '<td>';

					$doleditor = new DolEditor('supplier_description', $object->desc_supplier, '', 160, 'dolibarr_details', '', false, true, getDolGlobalInt('FCKEDITOR_ENABLE_DETAILS'), ROWS_4, '90%');
					$doleditor->Create();

					print '</td>';
					print '</tr>';
				}

				if (is_object($hookmanager)) {
					$parameters = array('id_fourn'=>!empty($id_fourn) ? $id_fourn : 0, 'prod_id'=>$object->id);
					$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);
					print $hookmanager->resPrint;
				}

				print '</table>';

				print dol_get_fiche_end();

				print '<div class="center">';
				print '<input class="button button-save" type="submit" value="'.$langs->trans("Save").'">';
				print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
				print '</div>';

				print '</form>'."\n";
			}


			// Actions buttons

			print '<div class="tabsAction">'."\n";

			if ($action != 'create_price' && $action != 'update_price') {
				$parameters = array();
				$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if (empty($reshook)) {
					if ($usercancreate) {
						print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/refbycustomer/prdrefbycustomer.php?id='.((int) $object->id).'&action=create_price&token='.newToken().'">';
						print ''.$langs->trans('AddSupplierRef').'</a>';
					}
				}
			}

			print "</div>\n";

			if ($user->hasRight("societe", "read")) { // Duplicate ? this check is already in the head of this file
				$param = '';
				if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
					$param .= '&contextpage='.urlencode($contextpage);
				}
				if ($limit > 0 && $limit != $conf->liste_limit) {
					$param .= '&limit='.((int) $limit);
				}
				$param .= '&ref='.urlencode($object->ref);

				// $product_fourn = new Productsociete($db);
				// $product_fourn_list = $product_fourn->list_product_fournisseur_price($object->id, $sortfield, $sortorder, $limit, $offset);
				// $product_fourn_list_all = $product_fourn->list_product_fournisseur_price($object->id, $sortfield, $sortorder, 0, 0);

				$filter = ['prod.rowid' => $id];
				$product_list = $prodcustref->fetchAll('', '', 0, 0, $filter);

				print_barre_liste($langs->trans('SocieteRef'), $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, '', '', '', 'title_accountancy.png', 0, '', '', $limit, 1);

				// Definition of fields for lists
				// Some fields are missing because they are not included in the database query
				$arrayfields = array(
					'pfp.datec'=>array('label'=>$langs->trans("AppliedPricesFrom"), 'checked'=>1, 'position'=>1),
					's.nom'=>array('label'=>$langs->trans("Suppliers"), 'checked'=>1, 'position'=>2),
					'pfp.fk_availability'=>array('label'=>$langs->trans("Availability"), 'enabled' => getDolGlobalInt('FOURN_PRODUCT_AVAILABILITY'), 'checked'=>0, 'position'=>4),
					'pfp.quantity'=>array('label'=>$langs->trans("QtyMin"), 'checked'=>1, 'position'=>5),
					'pfp.unitprice'=>array('label'=>$langs->trans("UnitPriceHT"), 'checked'=>1, 'position'=>9),
					'pfp.multicurrency_unitprice'=>array('label'=>$langs->trans("UnitPriceHTCurrency"), 'enabled' => isModEnabled('multicurrency'), 'checked'=>0, 'position'=>10),
					'pfp.charges'=>array('label'=>$langs->trans("Charges"), 'enabled' => !empty($conf->global->PRODUCT_CHARGES), 'checked'=>0, 'position'=>11),
					'pfp.delivery_time_days'=>array('label'=>$langs->trans("NbDaysToDelivery"), 'checked'=>-1, 'position'=>13),
					'pfp.supplier_reputation'=>array('label'=>$langs->trans("ReputationForThisProduct"), 'checked'=>-1, 'position'=>14),
					'pfp.fk_barcode_type'=>array('label'=>$langs->trans("BarcodeType"), 'enabled' => isModEnabled('barcode'), 'checked'=>0, 'position'=>15),
					'pfp.barcode'=>array('label'=>$langs->trans("BarcodeValue"), 'enabled' => isModEnabled('barcode'), 'checked'=>0, 'position'=>16),
					'pfp.packaging'=>array('label'=>$langs->trans("PackagingForThisProduct"), 'enabled' => getDolGlobalInt('PRODUCT_USE_SUPPLIER_PACKAGING'), 'checked'=>0, 'position'=>17),
					'pfp.status'=>array('label'=>$langs->trans("Status"), 'enabled' => 1, 'checked'=>0, 'position'=>40),
					'pfp.tms'=>array('label'=>$langs->trans("DateModification"), 'enabled' => isModEnabled('barcode'), 'checked'=>1, 'position'=>50),
				);

				// Selection of new fields
				include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

				$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
				$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields

				print '<form action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'" method="post" name="formulaire">';
				print '<input type="hidden" name="token" value="'.newToken().'">';
				print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
				print '<input type="hidden" name="action" value="list">';
				print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
				print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

				// Suppliers list title
				print '<div class="div-table-responsive">';
				print '<table class="liste centpercent">';

				$param = "&id=".$object->id;

				$nbfields = 0;

				print '<tr class="liste_titre">';
				if (!empty($arrayfields['pfp.datec']['checked'])) {
					print_liste_field_titre("Date de création", $_SERVER["PHP_SELF"], "pfp.datec", "", $param, "", $sortfield, $sortorder, '', '', 1);
					$nbfields++;
				}
				if (!empty($arrayfields['s.nom']['checked'])) {
					print_liste_field_titre("Societe", $_SERVER["PHP_SELF"], "s.nom", "", $param, "", $sortfield, $sortorder, '', '', 1);
					$nbfields++;
				}
				print_liste_field_titre("SocieteRef", $_SERVER["PHP_SELF"], "", "", $param, "", $sortfield, $sortorder, '', '', 1);
				$nbfields++;
				if (!empty($arrayfields['pfp.status']['checked'])) {
					print_liste_field_titre("Status", $_SERVER["PHP_SELF"], "pfp.status", "", $param, '', $sortfield, $sortorder, 'center ', '', 1);
					$nbfields++;
				}
				if (!empty($arrayfields['pfp.tms']['checked'])) {
					print_liste_field_titre("DateModification", $_SERVER["PHP_SELF"], "pfp.tms", "", $param, '', $sortfield, $sortorder, 'right ', '', 1);
					$nbfields++;
				}

				if (is_object($hookmanager)) {
					$parameters = array('id_fourn'=>(!empty($id_fourn)?$id_fourn:''), 'prod_id'=>$object->id, 'nbfields'=>$nbfields);
					$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object, $action);
				}
				print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ');
				$nbfields++;
				print "</tr>\n";

				if (is_array($product_list)) {
					foreach ($product_list as $productfourn) {
						$id = $productfourn->id;
						$productfourn = new Productrefbycustomer($db);
						$productfourn->fetch($id);
						print '<tr class="oddeven">';

						// Date from
						if (!empty($arrayfields['pfp.datec']['checked'])) {
							print '<td>'.dol_print_date(($productfourn->datec ? $productfourn->datec : $productfourn->datec), 'dayhour', 'tzuserrel').'</td>';
						}

						// Supplier
						if (!empty($arrayfields['s.nom']['checked'])) {
							print '<td class="tdoverflowmax150">'.$productfourn->getSocNomUrl($productfourn->fk_soc, 1, 'supplier').'</td>';
						}
						// Supplier ref
						print '<td class="tdoverflowmax150">'.$productfourn->ref_customer_prd.'</td>';

						// // Date modification
						// if (!empty($arrayfields['pfp.tms']['checked'])) {
						// 	print '<td class="right nowraponall">';
						// 	print dol_print_date(($productfourn->fourn_date_modification ? $productfourn->fourn_date_modification : $productfourn->date_modification), "dayhour");
						// 	print '</td>';
						// }

						// Extrafields
						// if (!empty($extralabels)) {
						// 	$sql  = "SELECT";
						// 	$sql .= " fk_object";
						// 	foreach ($extralabels as $key => $value) {
						// 		$sql .= ", ".$key;
						// 	}
						// 	$sql .= " FROM ".MAIN_DB_PREFIX."product_fournisseur_price_extrafields";
						// 	$sql .= " WHERE fk_object = ".((int) $productfourn->product_fourn_price_id);
						// 	$resql = $db->query($sql);
						// 	if ($resql) {
						// 		if ($db->num_rows($resql) != 1) {
						// 			foreach ($extralabels as $key => $value) {
						// 				if (!empty($arrayfields['ef.'.$key]['checked']) && !empty($extrafields->attributes["product_fournisseur_price"]['list'][$key]) && $extrafields->attributes["product_fournisseur_price"]['list'][$key] != 3) {
						// 					print "<td></td>";
						// 				}
						// 			}
						// 		} else {
						// 			$obj = $db->fetch_object($resql);
						// 			foreach ($extralabels as $key => $value) {
						// 				if (!empty($arrayfields['ef.'.$key]['checked']) && !empty($extrafields->attributes["product_fournisseur_price"]['list'][$key]) && $extrafields->attributes["product_fournisseur_price"]['list'][$key] != 3) {
						// 					print '<td align="right">'.$extrafields->showOutputField($key, $obj->{$key}, '', 'product_fournisseur_price')."</td>";
						// 				}
						// 			}
						// 		}
						// 		$db->free($resql);
						// 	}
						// }

						// Modify-Remove
						print '<td class="center nowraponall">';

						if ($usercancreate) {
							print '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?id='.((int) $object->id).'&socid='.((int) $productfourn->fk_soc).'&action=update_price&token='.newToken().'&rowid='.((int) $productfourn->id).'">'.img_edit()."</a>";
							print ' &nbsp; ';
							print '<a href="'.$_SERVER['PHP_SELF'].'?id='.((int) $object->id).'&socid='.((int) $productfourn->fk_soc).'&action=delete_customer_price&token='.newToken().'&rowid='.$productfourn->id.'">'.img_picto($langs->trans("Remove"), 'delete').'</a>';
						}

						print '</td>';

						print '</tr>';
					}

					if (empty($product_fourn_list)) {
						print '<tr><td colspan="'.$nbfields.'"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
					}
				} else {
					dol_print_error($db);
				}

				print '</table>';
				print '</div>';
				print '</form>';
			}
		}
	}
} else {
	print $langs->trans("ErrorUnknown");
}

// End of page
llxFooter();
$db->close();