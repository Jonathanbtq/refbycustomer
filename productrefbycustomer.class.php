<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014      Florian Henry   <florian.henry@open-concept.pro>
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
 * \file htdocs/product/class/productcustomerprice.class.php
 * \ingroup produit
 * \brief File of class to manage predefined price products or services by customer
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * File of class to manage predefined price products or services by customer
 */
class Productrefbycustomer extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'product_ref_by_customer';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'product_ref_by_customer';

	/**
	 * @var int Entity
	 */
	public $entity;

	public $datec = '';
	public $tms = '';

	/**
	 * @var int ID
	 */
	public $fk_product;

	/**
	 * @var int Thirdparty ID
	 */
	public $fk_soc;

	/**
	 * @var string Customer reference
	 */
	public $ref_customer_prd;

	/**
	 * @var int User ID
	 */
	public $fk_user;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that creates
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @param int $forceupdateaffiliate update price on each soc child
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0, $forceupdateaffiliate = 0)
	{

		global $conf, $langs;
		$error = 0;

		// Clean parameters

		if (isset($this->entity)) {
			$this->entity = trim($this->entity);
		}
		if (isset($this->fk_product)) {
			$this->fk_product = trim($this->fk_product);
		}
		if (isset($this->fk_soc)) {
			$this->fk_soc = trim($this->fk_soc);
		}
		if (isset($this->ref_customer_prd)) {
			$this->ref_customer_prd = trim($this->ref_customer_prd);
		}
		if (isset($this->fk_user)) {
			$this->fk_user = trim($this->fk_user);
		}
		if (isset($this->import_key)) {
			$this->import_key = trim($this->import_key);
		}

		// Insert request
		$sql = "INSERT INTO ".$this->db->prefix()."product_ref_by_customer(";
		$sql .= "entity,";
		$sql .= "datec,";
		$sql .= "fk_product,";
		$sql .= "fk_soc,";
		$sql .= 'ref_customer_prd,';
		$sql .= "fk_user,";
		$sql .= "import_key";
		$sql .= ") VALUES (";
		$sql .= " ".((int) $conf->entity).",";
		$sql .= " '".$this->db->idate(dol_now())."',";
		$sql .= " ".(!isset($this->fk_product) ? 'NULL' : "'".$this->db->escape($this->fk_product)."'").",";
		$sql .= " ".(!isset($this->fk_soc) ? 'NULL' : "'".$this->db->escape($this->fk_soc)."'").",";
		$sql .= " ".(!isset($this->ref_customer_prd) ? 'NULL' : "'".$this->db->escape($this->ref_customer_prd)."'").",";
		$sql .= " ".((int) $user->id).",";
		$sql .= " ".(!isset($this->import_key) ? 'NULL' : "'".$this->db->escape($this->import_key)."'");
		$sql .= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors [] = "Error ".$this->db->lasterror();
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id($this->db->prefix()."product_ref_by_customer");
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param 	int 	$id 	ID of customer price
	 * @return 	int 			<0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id)
	{
		global $langs;

		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.entity,";
		$sql .= " t.datec,";
		$sql .= " t.tms,";
		$sql .= " t.fk_product,";
		$sql .= " t.fk_soc,";
		$sql .= " t.ref_customer_prd,";
		$sql .= " t.fk_user,";
		$sql .= " t.import_key";
		$sql .= " FROM ".$this->db->prefix()."product_ref_by_customer as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;

				$this->entity = $obj->entity;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_product = $obj->fk_product;
				$this->fk_soc = $obj->fk_soc;
				$this->ref_customer_prd = $obj->ref_customer_prd;
				$this->fk_user = $obj->fk_user;
				$this->import_key = $obj->import_key;

				$this->db->free($resql);

				return 1;
			} else {
				$this->db->free($resql);

				return 0;
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $id Id societe
	 * @return void
	 */
	public function fetchBySoc($id, $idprod)
	{
		global $langs;

		$sql = "SELECT";
		$sql .= "*";
		$sql .= " FROM ".$this->db->prefix()."product_ref_by_customer as t";
		$sql .= " WHERE t.fk_soc = ".((int) $id);
		$sql .= " AND t.fk_product = ".((int) $idprod);

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;

				$this->entity = $obj->entity;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_product = $obj->fk_product;
				$this->fk_soc = $obj->fk_soc;
				$this->ref_customer_prd = $obj->ref_customer_prd;
				$this->fk_user = $obj->fk_user;
				$this->import_key = $obj->import_key;

				$this->db->free($resql);

				return $this;
			} else {
				$this->db->free($resql);

				return 0;
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Load all customer prices in memory from database
	 *
	 * @param 	string 	$sortorder 	order
	 * @param 	string 	$sortfield 	field
	 * @param 	int 	$limit 		page
	 * @param 	int 	$offset 	offset
	 * @param 	array 	$filter 	Filter for select
	 * @deprecated since dolibarr v17 use fetchAll
	 * @return 	int 				<0 if KO, >0 if OK
	 */
	public function fetch_all($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array())
	{
		// phpcs:enable

		dol_syslog(get_class($this)."::fetch_all is deprecated, use fetchAll instead", LOG_NOTICE);

		return $this->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);
	}

	/**
	 * Load all customer prices in memory from database
	 *
	 * @param 	string 	$sortorder 	order
	 * @param 	string 	$sortfield 	field
	 * @param 	int 	$limit 		page
	 * @param 	int 	$offset 	offset
	 * @param 	array 	$filter 	Filter for select
	 * @return 	int 				<0 if KO, >0 if OK
	 * @since dolibarr v17
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array())
	{
		global $langs;

		if (empty($sortfield)) {
			$sortfield = "t.rowid";
		}
		if (empty($sortorder)) {
			$sortorder = "DESC";
		}

		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.entity,";
		$sql .= " t.datec,";
		$sql .= " t.tms,";
		$sql .= " t.fk_product,";
		$sql .= " t.fk_soc,";
		$sql .= " t.ref_customer_prd,";
		$sql .= " t.fk_user,";
		$sql .= " t.import_key,";
		$sql .= " soc.nom as socname,";
		$sql .= " prod.ref as prodref";
		$sql .= " FROM ".$this->db->prefix()."product_ref_by_customer as t,";
		$sql .= " ".$this->db->prefix()."product as prod,";
		$sql .= " ".$this->db->prefix()."societe as soc";
		$sql .= " WHERE soc.rowid=t.fk_soc ";
		$sql .= " AND prod.rowid=t.fk_product ";
		// $sql .= " AND prod.entity IN (".getEntity('product').")";
		// $sql .= " AND t.entity IN (".getEntity('productprice').")";
		// Manage filter
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if (strpos($key, 'date')) {				// To allow $filter['YEAR(s.dated)']=>$year
					$sql .= " AND ".$key." = '".$this->db->escape($value)."'";
				} elseif ($key == 'soc.nom') {
					$sql .= " AND ".$key." LIKE '%".$this->db->escape($value)."%'";
				} elseif ($key == 'prod.ref' || $key == 'prod.label') {
					$sql .= " AND ".$key." LIKE '%".$this->db->escape($value)."%'";
				} elseif ($key == 't.price' || $key == 't.price_ttc') {
					$sql .= " AND ".$key." LIKE '%".price2num($value)."%'";
				} else {
					$sql .= " AND ".$key." = ".((int) $value);
				}
			}
		}
		$sql .= $this->db->order($sortfield, $sortorder);
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this)."::fetchAll", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array();
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new RefByCustomerLine();

				$line->id = $obj->rowid;

				$line->entity = $obj->entity;
				$line->datec = $this->db->jdate($obj->datec);
				$line->tms = $this->db->jdate($obj->tms);
				$line->fk_product = $obj->fk_product;
				$line->fk_soc = $obj->fk_soc;
				$line->ref_customer_prd = $obj->ref_customer_prd;
				$line->fk_user = $obj->fk_user;
				$line->import_key = $obj->import_key;

				$this->lines[] = $line;
			}
			$this->db->free($resql);

			return $this->lines;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}

		/**
	 *	Display supplier of product
	 *
	 *	@param	int		$withpicto		Add picto
	 *	@param	string	$option			Target of link ('', 'customer', 'prospect', 'supplier')
	 *	@param	int		$maxlen			Max length of name
	 *  @param	integer	$notooltip		1=Disable tooltip
	 *	@return	string					String with supplier price
	 *  TODO Remove this method. Use getNomUrl directly.
	 */
	public function getSocNomUrl($soc_id, $withpicto = 0, $option = 'supplier', $maxlen = 0, $notooltip = 0)
	{
		$societe = new Societe($this->db);
		$societe->fetch($soc_id);

		return $societe->getNomUrl($withpicto, $option, $maxlen, $notooltip);
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modifies
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @param int $forceupdateaffiliate update price on each soc child
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($user = 0, $notrigger = 0, $forceupdateaffiliate = 0)
	{

		global $conf, $langs;
		$error = 0;

		// Clean parameters

		if (isset($this->entity)) {
			$this->entity = trim($this->entity);
		}
		if (isset($this->fk_product)) {
			$this->fk_product = trim($this->fk_product);
		}
		if (isset($this->fk_soc)) {
			$this->fk_soc = trim($this->fk_soc);
		}
		if (isset($this->ref_customer_prd)) {
			$this->ref_customer_prd = trim($this->ref_customer_prd);
		}
		if (isset($this->fk_user)) {
			$this->fk_user = trim($this->fk_user);
		}
		if (isset($this->import_key)) {
			$this->import_key = trim($this->import_key);
		}

		// Update request
		$sql = "UPDATE ".$this->db->prefix()."product_ref_by_customer SET";

		$sql .= " entity=".$conf->entity.",";
		$sql .= " datec='".$this->db->idate(dol_now())."',";
		$sql .= " tms=".(dol_strlen($this->tms) != 0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql .= " fk_product=".(isset($this->fk_product) ? $this->fk_product : "null").",";
		$sql .= " fk_soc=".(isset($this->fk_soc) ? $this->fk_soc : "null").",";
		$sql .= " ref_customer_prd=".(isset($this->ref_customer_prd) ? "'".$this->db->escape($this->ref_customer_prd)."'" : "null").",";
		$sql .= " fk_user=".$user->id.",";
		$sql .= " import_key=".(isset($this->import_key) ? "'".$this->db->escape($this->import_key)."'" : "null");
		$sql .= " WHERE rowid=".((int) $this->id);

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors [] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user that deletes
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		$this->db->begin();

		if (!$error) {
			$sql = "DELETE FROM ".$this->db->prefix()."product_ref_by_customer";
			$sql .= " WHERE rowid=".((int) $this->id);

			dol_syslog(get_class($this)."::delete", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				$error++;
				$this->errors [] = "Error ".$this->db->lasterror();
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{

		$this->id = 0;

		$this->entity = '';
		$this->datec = '';
		$this->tms = '';
		$this->fk_product = '';
		$this->fk_soc = '';
		$this->ref_customer_prd = '';
		$this->fk_user = '';
		$this->import_key = '';
	}
}

/**
 * File of class to manage predefined price products or services by customer lines
 */
class RefByCustomerLine
{
	/**
	 * @var int ID
	 */
	public $id;

	/**
	 * @var int Entity
	 */
	public $entity;

	public $datec = '';
	public $tms = '';

	/**
	 * @var int ID
	 */
	public $fk_product;

	/**
	 * @var string Customer reference
	 */
	public $ref_customer_prd;

	/**
	 * @var int Thirdparty ID
	 */
	public $fk_soc;

	/**
	 * @var int User ID
	 */
	public $fk_user;

	public $import_key;
}