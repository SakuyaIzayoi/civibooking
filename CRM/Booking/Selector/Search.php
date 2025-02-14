<?php
use CRM_Booking_ExtensionUtil as E; 
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Booking_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API {

  /**
   * This defines two actions- View and Edit.
   *
   * @var array
   * @static
   */
  static $_links = NULL;

  /**
   * we use desc to remind us what that column is, name is used in the tpl
   *
   * @var array
   * @static
   */
  static $_columnHeaders;

  /**
   * Properties of contact we're interested in displaying
   * @var array
   * @static
   */
  static $_properties = array(
    'contact_id',
    'booking_id',
    'contact_type',
    'sort_name',
    'booking_title',
    'booking_status_id',
    'booking_status',
    'booking_payment_status',
    'booking_total_amount',
    'booking_event_date',
    'booking_start_date',
    'booking_end_date',
    'booking_associated_contact_id',
    'booking_associated_contact',
    'booking_created_date',
  );

  /**
   * are we restricting ourselves to a single contact
   *
   * @access protected
   * @var boolean
   */
  protected $_single = FALSE;

  /**
   * are we restricting ourselves to a single contact
   *
   * @access protected
   * @var boolean
   */
  protected $_limit = NULL;

  /**
   * what context are we being invoked from
   *
   * @access protected
   * @var string
   */
  protected $_context = NULL;

  /**
   * queryParams is the array returned by exportValues called on
   * the HTML_QuickForm_Controller for that page.
   *
   * @var array
   * @access protected
   */
  public $_queryParams;

  /**
   * represent the type of selector
   *
   * @var int
   * @access protected
   */
  protected $_action;

  /**
   * The additional clause that we restrict the search with
   *
   * @var string
   */
  protected $_bookingClause = NULL;

  /**
   * The query object
   *
   * @var string
   */
  protected $_query;

  /**
   * Class constructor
   *
   * @param array   $queryParams array of parameters for query
   * @param int     $action - action of search basic or advanced.
   * @param string  $bookingClause if the caller wants to further restrict the search (used in bookings)
   * @param boolean $single are we dealing only with one contact?
   * @param int     $limit  how many booking do we want returned
   *
   * @return CRM_Contact_Selector
   * @access public
   */ function __construct(&$queryParams,
    $action       = CRM_Core_Action::NONE,
    $bookingClause = NULL,
    $single       = FALSE,
    $limit        = NULL,
    $context      = 'search'
  ) {

    // submitted form values
    $this->_queryParams = &$queryParams;


    $this->_single  = $single;
    $this->_limit   = $limit;
    $this->_context = $context;

    $this->_bookingClause = $bookingClause;

    // type of selector
    $this->_action = $action;

    /*
    $bookingQuery = CRM_Booking_BAO_Query::defaultReturnProperties(
      CRM_Booking_BAO_BookingContactQuery::MODE_BOOKING,
      FALSE
    );

    $this->_query = new CRM_Booking_BAO_BookingContactQuery(
      $this->_queryParams,
      $bookingQuery,
      NULL,
      FALSE,
      FALSE,
      CRM_Booking_BAO_BookingContactQuery::MODE_BOOKING
    );*/


    $defaultReturnProperties = CRM_Booking_BAO_Query::defaultReturnProperties();

    $this->_query = new CRM_Contact_BAO_Query(
      $this->_queryParams,
      $defaultReturnProperties,
      NULL,
      FALSE,
      FALSE,
      CRM_Contact_BAO_Query::MODE_CONTACTS
    );


    $this->_query->_distinctComponentClause = " civicrm_booking.id";
    $this->_query->_groupByComponentClause = " GROUP BY civicrm_booking.id ";

  }
  //end of constructor



  /**
   * This method returns the links that are given for each search row.
   * currently the links added for each row are
   *
   * - View
   * - Edit
   *
   * @return array
   * @access public
   *
   */
  static function &links($qfKey = NULL, $context = NULL) {
    $extraParams = NULL;
    if ($qfKey) {
      $extraParams .= "&key={$qfKey}";
    }


    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::VIEW => array(
          'name' => E::ts('View'),
          'url' => 'civicrm/contact/view/booking',
          'qs' => 'reset=1&id=%%id%%&cid=%%cid%%&action=view&context=%%cxt%%&selectedChild=booking' . $extraParams,
          'title' => E::ts('View Booking'),
        ),
        CRM_Core_Action::UPDATE => array(
          'name' => E::ts('Edit'),
          'url' => 'civicrm/booking/edit',
          'qs' => 'reset=1&action=update&id=%%id%%&cid=%%cid%%&context=%%cxt%%' . $extraParams,
          'title' => E::ts('Edit Booking'),
        ),
        CRM_Core_Action::BASIC => array(
          'name' => E::ts('Update Status'),
          'url' => 'civicrm/contact/view/booking',
          'qs' => 'reset=1&action=update&id=%%id%%&cid=%%cid%%&context=%%cxt%%' . $extraParams,
          'title' => E::ts('Update Status'),
        ),
        CRM_Core_Action::ADVANCED => array(
          'name' => E::ts('Record Payment'),
          'url' => 'civicrm/contact/view/booking',
          'qs' => 'reset=1&action=update&id=%%id%%&cid=%%cid%%&context=%%cxt%%' . $extraParams,
          'title' => E::ts('Edit Booking'),
        ),

        CRM_Core_Action::CLOSE => array(
          'name' => E::ts('Cancel'),
          'url' => 'civicrm/contact/view/booking',
          'qs' => 'reset=1&action=close&id=%%id%%&cid=%%cid%%&context=%%cxt%%' . $extraParams,
          'title' => E::ts('Edit Booking'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => E::ts('Delete'),
          'url' => 'civicrm/contact/view/booking',
          'qs' => 'reset=1&action=delete&id=%%id%%&cid=%%cid%%&context=%%cxt%%' . $extraParams,
          'title' => E::ts('Delete Booking'),
        ),
      );

    }
    return self::$_links;
  }
  //end of function


  /**
   * getter for array of the parameters required for creating pager.
   *
   * @param
   * @access public
   */
  function getPagerParams($action, &$params) {
    $params['status'] = E::ts('Booking') . ' %%StatusMessage%%';
    $params['csvString'] = NULL;
    if ($this->_limit) {
      $params['rowCount'] = $this->_limit;
    }
    else {
      $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
    }

    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
  }
  //end of function


  /**
   * Returns total number of rows for the query.
   *
   * @param
   *
   * @return int Total number of rows
   * @access public
   */
  function getTotalCount($action) {
    return $this->_query->searchQuery(0, 0, NULL,
      TRUE, FALSE,
      FALSE, FALSE,
      FALSE,
      $this->_bookingClause
    );
  }

  /**
   * returns all the rows in the given offset and rowCount
   *
   * @param enum   $action   the action being performed
   * @param int    $offset   the row number to start from
   * @param int    $rowCount the number of rows to return
   * @param string $sort     the sql string that describes the sort order
   * @param enum   $output   what should the result set include (web/email/csv)
   *
   * @return int   the total number of rows for this action
   */
  function &getRows($action, $offset, $rowCount, $sort, $output = NULL) {
    $result = $this->_query->searchQuery($offset, $rowCount, $sort,
      FALSE, FALSE,
      FALSE, FALSE,
      FALSE, 
      $this->_bookingClause
    );


   //lets handle view, edit and delete separately.
    $permissions = array(CRM_Core_Permission::VIEW, CRM_Core_Permission::EDIT, CRM_Core_Permission::DELETE);
    /*if (CRM_Core_Permission::check('edit event Booking')) {
      $permissions[] = CRM_Core_Permission::EDIT;
    }
    if (CRM_Core_Permission::check('delete in Booking')) {
      $permissions[] = CRM_Core_Permission::DELETE;
    }*/
    $mask = CRM_Core_Action::mask($permissions);


    // process the result of the query
    $rows = array();


    $params = array(
      'option_group_name' => CRM_Booking_Utils_Constants::OPTION_BOOKING_STATUS,
      'name' => CRM_Booking_Utils_Constants::OPTION_VALUE_CANCELLED,
    );
    $ov = civicrm_api3('OptionValue', 'get', $params);
    $cancelStatusId = CRM_Utils_Array::value('value', CRM_Utils_Array::value($ov['id'], $ov['values']));
    while ($result->fetch()) {
      $row = array();
      //Fixed - CVB-84
      //Make sure we don't return contact that doesn't have booking
      if(!$result->booking_id){
        continue;
      }

      // the columns we are interested in
      foreach (self::$_properties as $property) {
        if (property_exists($result, $property)) {
          $row[$property] = $result->$property;
        }

        $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contact_id;

        $isCancelled = FALSE;
        if($result->booking_status_id == $cancelStatusId){
          $isCancelled = TRUE;
        }
        $links = self::links($this->_key, $this->_context);
        if($isCancelled){
          unset($links[CRM_Core_Action::UPDATE]);
          unset($links[CRM_Core_Action::BASIC]);
          unset($links[CRM_Core_Action::CLOSE]);
        }
        //Fixed CVB-144
        if($result->booking_payment_status_id){
            unset($links[CRM_Core_Action::ADVANCED]);
        }

        $row['action'] = CRM_Core_Action::formLink($links,
          $mask,
          array(
            'id' => $result->booking_id,
            'cid' => $result->contact_id,
            'cxt' => $this->_context,
          ),
          'more',
          FALSE,
          $action,
          'Booking',
          $result->booking_id
        );
      }
      $rows[] = $row;
    }
    return $rows;
  }

  /**
   *
   * @return array              $qill         which contains an array of strings
   * @access public
   */

  // the current internationalisation is bad, but should more or less work
  // for most of "European" languages
  public function getQILL() {
    return $this->_query->qill();
  }

   /**
   * returns the column headers as an array of tuples:
   * (name, sortName (key to the sort array))
   *
   * @param string $action the action being performed
   * @param enum   $output what should the result set include (web/email/csv)
   *
   * @return array the column headers that need to be displayed
   * @access public
   */
  public function &getColumnHeaders($action = NULL, $output = NULL) {
    if (!isset(self::$_columnHeaders)) {
      self::$_columnHeaders = array(
        array('name' => E::ts('Title'),
          'sort' => 'booking_title',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
        // array(
          // 'name' => E::ts('Start Date'),
          // 'sort' => 'booking_start_date',
          // 'direction' => CRM_Utils_Sort::DONTCARE,
        // ),
        array(
          'name' => E::ts('Associated Contact'),
          'sort' => 'booking_associated_contact',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
        array(
          'name' => E::ts('Date Booking Made'),
          'sort' => 'booking_event_date',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
		array(
          'name' => E::ts('Start Date'),
          'sort' => 'start_date',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
		array(
          'name' => E::ts('End Date'),
          'sort' => 'end_date',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
        array(
          'name' => E::ts('Price'),
          'sort' => 'booking_total_amount',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),

        array(
          'name' => E::ts('Booking Status'),
          'sort' => 'booking_status',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),
        array(
          'name' => E::ts('Payment Status'),
          'sort' => 'booking_payment_status',
          'direction' => CRM_Utils_Sort::DONTCARE,
        ),

        array('desc' => E::ts('Actions')),
      );

      if (!$this->_single) {
        $pre = array(
          array('desc' => E::ts('Contact Type')),
          array(
            'name' => E::ts('Booking Contact'),
            'sort' => 'sort_name',
            'direction' => CRM_Utils_Sort::DONTCARE,
          ),
        );
        self::$_columnHeaders = array_merge($pre, self::$_columnHeaders);
      }
    }
    return self::$_columnHeaders;
  }


  function alphabetQuery() {
    return $this->_query->searchQuery(NULL, NULL, NULL, FALSE, FALSE, TRUE);
  }

  function &getQuery() {
    return $this->_query;
  }

  /**
   * name of export file.
   *
   * @param string $output type of output
   *
   * @return string name of the file
   */
  function getExportFileName($output = 'csv') {
    return E::ts('CiviCRM Booking Search');
  }
}
//end of class

