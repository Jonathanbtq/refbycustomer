<?php
/* Copyright (C) 2023		Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2024		SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modRefbycustomer_RefbycustomerTriggers.class.php
 * \ingroup refbycustomer
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modRefbycustomer_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for Refbycustomer module
 */
class InterfaceRefbycustomerTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		parent::__construct($db);
		$this->family = "demo";
		$this->description = "Refbycustomer triggers.";
		$this->version = self::VERSIONS['dev'];
		$this->picto = 'refbycustomer@refbycustomer';
	}

	/**
	 * Function called when a Dolibarr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		Return integer <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (!isModEnabled('refbycustomer')) {
			return 0; // If module is not enabled, we do nothing
		}

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		}

		// Or you can execute some code here
		switch ($action) {  // @phan-suppress-current-line PhanNoopSwitchCases
			// Users
			//case 'USER_CREATE':
			//case 'USER_MODIFY':
			//case 'USER_NEW_PASSWORD':
			//case 'USER_ENABLEDISABLE':
			//case 'USER_DELETE':

			// Actions
			//case 'ACTION_MODIFY':
			//case 'ACTION_CREATE':
			//case 'ACTION_DELETE':

			// Groups
			//case 'USERGROUP_CREATE':
			//case 'USERGROUP_MODIFY':
			//case 'USERGROUP_DELETE':

			// Companies
			//case 'COMPANY_CREATE':
			//case 'COMPANY_MODIFY':
			//case 'COMPANY_DELETE':

			// Contacts
			//case 'CONTACT_CREATE':
			//case 'CONTACT_MODIFY':
			//case 'CONTACT_DELETE':
			//case 'CONTACT_ENABLEDISABLE':

			// Products
			//case 'PRODUCT_CREATE':
			//case 'PRODUCT_MODIFY':
			//case 'PRODUCT_DELETE':
			//case 'PRODUCT_PRICE_MODIFY':
			//case 'PRODUCT_SET_MULTILANGS':
			//case 'PRODUCT_DEL_MULTILANGS':

			//Stock movement
			//case 'STOCK_MOVEMENT':

			//MYECMDIR
			//case 'MYECMDIR_CREATE':
			//case 'MYECMDIR_MODIFY':
			//case 'MYECMDIR_DELETE':

			// Sales orders
			//case 'ORDER_CREATE':
			//case 'ORDER_MODIFY':
			//case 'ORDER_VALIDATE':
			//case 'ORDER_DELETE':
			//case 'ORDER_CANCEL':
			//case 'ORDER_SENTBYMAIL':
			//case 'ORDER_CLASSIFY_BILLED':		// TODO Replace it with ORDER_BILLED
			//case 'ORDER_CLASSIFY_UNBILLED':	// TODO Replace it with ORDER_UNBILLED
			//case 'ORDER_SETDRAFT':
			//case 'LINEORDER_INSERT':
			//case 'LINEORDER_UPDATE':
			//case 'LINEORDER_DELETE':

			// Supplier orders
			//case 'ORDER_SUPPLIER_CREATE':
			//case 'ORDER_SUPPLIER_MODIFY':
			//case 'ORDER_SUPPLIER_VALIDATE':
			//case 'ORDER_SUPPLIER_DELETE':
			//case 'ORDER_SUPPLIER_APPROVE':
			//case 'ORDER_SUPPLIER_CLASSIFY_BILLED':		// TODO Replace with ORDER_SUPPLIER_BILLED
			//case 'ORDER_SUPPLIER_CLASSIFY_UNBILLED':		// TODO Replace with ORDER_SUPPLIER_UNBILLED
			//case 'ORDER_SUPPLIER_REFUSE':
			//case 'ORDER_SUPPLIER_CANCEL':
			//case 'ORDER_SUPPLIER_SENTBYMAIL':
			//case 'ORDER_SUPPLIER_RECEIVE':
			//case 'LINEORDER_SUPPLIER_DISPATCH':
			//case 'LINEORDER_SUPPLIER_CREATE':
			//case 'LINEORDER_SUPPLIER_UPDATE':
			//case 'LINEORDER_SUPPLIER_DELETE':

			// Proposals
			//case 'PROPAL_CREATE':
			//case 'PROPAL_MODIFY':
			//case 'PROPAL_VALIDATE':
			//case 'PROPAL_SENTBYMAIL':
			//case 'PROPAL_CLASSIFY_BILLED':		// TODO Replace it with PROPAL_BILLED
			//case 'PROPAL_CLASSIFY_UNBILLED':		// TODO Replace it with PROPAL_UNBILLED
			//case 'PROPAL_CLOSE_SIGNED':
			//case 'PROPAL_CLOSE_REFUSED':
			//case 'PROPAL_DELETE':
			//case 'LINEPROPAL_INSERT':
			//case 'LINEPROPAL_UPDATE':
			//case 'LINEPROPAL_DELETE':

			// SupplierProposal
			//case 'SUPPLIER_PROPOSAL_CREATE':
			//case 'SUPPLIER_PROPOSAL_MODIFY':
			//case 'SUPPLIER_PROPOSAL_VALIDATE':
			//case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
			//case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
			//case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
			//case 'SUPPLIER_PROPOSAL_DELETE':
			//case 'LINESUPPLIER_PROPOSAL_INSERT':
			//case 'LINESUPPLIER_PROPOSAL_UPDATE':
			//case 'LINESUPPLIER_PROPOSAL_DELETE':

			// Contracts
			//case 'CONTRACT_CREATE':
			//case 'CONTRACT_MODIFY':
			//case 'CONTRACT_ACTIVATE':
			//case 'CONTRACT_CANCEL':
			//case 'CONTRACT_CLOSE':
			//case 'CONTRACT_DELETE':
			//case 'LINECONTRACT_INSERT':
			//case 'LINECONTRACT_UPDATE':
			//case 'LINECONTRACT_DELETE':

			// Bills
			//case 'BILL_CREATE':
			//case 'BILL_MODIFY':
			//case 'BILL_VALIDATE':
			//case 'BILL_UNVALIDATE':
			//case 'BILL_SENTBYMAIL':
			//case 'BILL_CANCEL':
			//case 'BILL_DELETE':
			//case 'BILL_PAYED':
			//case 'LINEBILL_INSERT':
			//case 'LINEBILL_UPDATE':
			//case 'LINEBILL_DELETE':

			// Recurring Bills
			//case 'BILLREC_MODIFY':
			//case 'BILLREC_DELETE':
			//case 'BILLREC_AUTOCREATEBILL':
			//case 'LINEBILLREC_MODIFY':
			//case 'LINEBILLREC_DELETE':

			//Supplier Bill
			//case 'BILL_SUPPLIER_CREATE':
			//case 'BILL_SUPPLIER_UPDATE':
			//case 'BILL_SUPPLIER_DELETE':
			//case 'BILL_SUPPLIER_PAYED':
			//case 'BILL_SUPPLIER_UNPAYED':
			//case 'BILL_SUPPLIER_VALIDATE':
			//case 'BILL_SUPPLIER_UNVALIDATE':
			//case 'LINEBILL_SUPPLIER_CREATE':
			//case 'LINEBILL_SUPPLIER_UPDATE':
			//case 'LINEBILL_SUPPLIER_DELETE':

			// Payments
			//case 'PAYMENT_CUSTOMER_CREATE':
			//case 'PAYMENT_SUPPLIER_CREATE':
			//case 'PAYMENT_ADD_TO_BANK':
			//case 'PAYMENT_DELETE':

			// Online
			//case 'PAYMENT_PAYBOX_OK':
			//case 'PAYMENT_PAYPAL_OK':
			//case 'PAYMENT_STRIPE_OK':

			// Donation
			//case 'DON_CREATE':
			//case 'DON_UPDATE':
			//case 'DON_DELETE':

			// Interventions
			//case 'FICHINTER_CREATE':
			//case 'FICHINTER_MODIFY':
			//case 'FICHINTER_VALIDATE':
			//case 'FICHINTER_CLASSIFY_BILLED':			// TODO Replace it with FICHINTER_BILLED
			//case 'FICHINTER_CLASSIFY_UNBILLED':		// TODO Replace it with FICHINTER_UNBILLED
			//case 'FICHINTER_DELETE':
			//case 'LINEFICHINTER_CREATE':
			//case 'LINEFICHINTER_UPDATE':
			//case 'LINEFICHINTER_DELETE':

			// Members
			//case 'MEMBER_CREATE':
			//case 'MEMBER_VALIDATE':
			//case 'MEMBER_SUBSCRIPTION':
			//case 'MEMBER_MODIFY':
			//case 'MEMBER_NEW_PASSWORD':
			//case 'MEMBER_RESILIATE':
			//case 'MEMBER_DELETE':

			// Categories
			//case 'CATEGORY_CREATE':
			//case 'CATEGORY_MODIFY':
			//case 'CATEGORY_DELETE':
			//case 'CATEGORY_SET_MULTILANGS':

			// Projects
			//case 'PROJECT_CREATE':
			//case 'PROJECT_MODIFY':
			//case 'PROJECT_DELETE':

			// Project tasks
			//case 'TASK_CREATE':
			//case 'TASK_MODIFY':
			//case 'TASK_DELETE':

			// Task time spent
			//case 'TASK_TIMESPENT_CREATE':
			//case 'TASK_TIMESPENT_MODIFY':
			//case 'TASK_TIMESPENT_DELETE':
			//case 'PROJECT_ADD_CONTACT':
			//case 'PROJECT_DELETE_CONTACT':
			//case 'PROJECT_DELETE_RESOURCE':

			// Shipping
			//case 'SHIPPING_CREATE':
			//case 'SHIPPING_MODIFY':
			//case 'SHIPPING_VALIDATE':
			//case 'SHIPPING_SENTBYMAIL':
			//case 'SHIPPING_BILLED':
			//case 'SHIPPING_CLOSED':
			//case 'SHIPPING_REOPEN':
			//case 'SHIPPING_DELETE':

			// and more...

			default:
				dol_syslog("Trigger '".$this->name."' for action '".$action."' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}

	public function setRefCustomProduct($object, $type, $user)
	{
		switch ($type) {
			case 'commande':
				$comm = new Commande($this->db);
				$comm->fetch($object->fk_commande);
				break;
			case 'commandefourn':
				$comm = new CommandeFournisseur($this->db);
				$comm->fetch($object->fk_commande);
				break;
			case 'facture':
				$comm = new Facture($this->db);
				$comm->fetch($object->fk_facture);
				break;
			case 'facturesuppl':
				$comm = new FactureFournisseur($this->db);
				$comm->fetch($object->fk_facture_fourn);
				break;
			case 'propal':
				$comm = new Propal($this->db);
				$comm->fetch($object->fk_propal);
				break;
			case 'propalsuppl':
				$comm = new SupplierProposal($this->db);
				$comm->fetch($object->fk_supplier_proposal);
				break;
			default:
				$comm = null;
				break;
		}

		if ($comm) {
			$refcustom = GETPOST('refcustom') ?? '';
			if (!empty($refcustom) && !empty($object->fk_product) && !empty($comm->socid)) {
				$sqlSelect = 'SELECT * FROM '.MAIN_DB_PREFIX.'product_ref_by_customer WHERE fk_product = '.$object->fk_product.' AND fk_soc ='. $comm->socid;
				if ($sqlSelect = $this->db->query($sqlSelect)) {
					$sqlSelect = $this->db->fetch_object($sqlSelect);
				}
	
				if ($sqlSelect && $sqlSelect->ref_customer_prd === $refcustom) {
					setEventMessage('Cette référence est déjà présente pour ce produit');
				} else {
					$sqlDel = 'DELETE FROM '.MAIN_DB_PREFIX.'product_ref_by_customer WHERE fk_product = '.$object->fk_product.' AND fk_soc ='. $comm->socid;
					$sqlDel = $this->db->query($sqlDel);
					if ($sqlDel) {
						$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'product_ref_by_customer (entity, datec, fk_soc, fk_product, fk_user, import_key, ref_customer_prd) VALUES("", "'.$this->db->idate(dol_now()).'" ,'.$comm->socid.', '.$object->fk_product.', '.$user->id.', 1,"'.$refcustom.'")';
						if (!$this->db->query($sql)) {
							setEventMessage('Une erreur est survenue lors de la sauvegarde des données', 'errors');
						} else {
							setEventMessage('Référence sauvegardé', 'mesgs');
						}
					}
				}
			}
		}
	}

	public function handleLineAction(string $action, $object, User $user, Translate $langs, Conf $conf, string $type)
	{
		$this->setRefCustomProduct($object, $type, $user);
		return 0;
	}

	public function __call($name, $arguments)
	{
		global $conf;

		$typeMap = [
			'lineorder' => 'commande',
			'linepropal' => 'propal',
			'linebill' => 'facture',
			'linebillSupplier' => 'facturesuppl',
			'lineorderSupplier' => 'commandefourn',
			'linesupplierProposalInsert' => 'propalsuppl'
		];
	
		// Trier le tableau en fonction de la longueur des clés, de la plus longue à la plus courte
		// Eviter que linebillsuppl ne soit pas prit en compte car linebill est plus court
		uksort($typeMap, function($a, $b) {
			return strlen($b) - strlen($a);
		});

		$selectedType = null;
		foreach ($typeMap as $key => $typeName) {
			if (strpos($name, $key) === 0) {
				$num = null;
				
				switch ($typeName) {
					case 'commande';
					$num = '1';
					break;
					case 'propal';
					$num = '2';
					break;
					case 'facture';
					$num = '3';
					break;
					case 'facturesuppl';
					$num = '4';
					break;
					case 'commandefourn';
					$num = '5';
					break;
					case 'propalsuppl';
					$num = '6';
					break;
				}
				if ($num && isset($conf->global->{"REFBYCUSTOMER_MYPARAM" . $num})) {
					$selectedType = $typeName;
					break;
				}
			}
		}
		
		if ($selectedType) {
			return $this->handleLineAction($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $selectedType);
		}

		return 0;
	}
}
