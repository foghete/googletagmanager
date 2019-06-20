<?php
/**
* NOTICE OF LICENSE
**
*  @author    Rodrigo Varela Tabuyo <rodrigo@centolaecebola.com>
*  @copyright 2017 Rodrigo Varela Tabuyo
*  @license   ……
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class GoogleTagManager extends Module
{
    // add custom error messages
    protected $errors = array();
    protected $gtm_id;
    public function __construct()
    {
        $this->name = 'googletagmanager';
        $this->tab = 'analytics_stats';
        $this->version = '0.1';
        $this->author = 'Rodrigo Varela Tabuyo';
        $this->module_key = '5cb794a64177c47254bef97263fe8lbc';
        $this->bootstrap = false;
        $this->ps_versions_compliancy = array('min' => '1.6');

        parent::__construct();

        $this->displayName = $this->l('Google Tag Manager');
        $this->description = $this->l('Añade tags y no mires atrás');

        $this->confirmUninstall = $this->l('¿Está seguro de quieres desinstalar este módulo?');

        $this->$gmt_id = Configuration::get('GTM_ID');
    }

    public function install()
    {
        // Simple database to register orders set to GA
        
        return (
            parent::install()

            // Use to set common dataLayer vars
            && $this->registerHook('displayHeader')

            // Use to set Product page dataLayer vars
            && $this->registerHook('displayFooter')
            
            // Use to set order confirmation dataLayer vars
            && $this->registerHook('displayOrderConfirmation')
            );
    }

    public function uninstall() {

        // Uninstall Module
        if (!parent::uninstall()) {
            return false;
        }

        return parent::uninstall();
    }

    public function hookDisplayHeader($params) {
        //Should be as high as possible in the head section
        return $this->display(__FILE__, 'views/templates/hooks/googletagmanagerontop.tpl');

    }
    public function hookDisplayTop() {
        // Custom hook to add iframe right after <body>. It seems to work fine here
        $this->context->smarty->assign('GTM_ID', $this->gtm_id);
        return $this->display(__FILE__, 'views/templates/hooks/googletagmanagerafterbody.tpl');
    }
    
    

    public function hookDisplayFooter($params) {
        //Set up common Criteo One Tag vars
        $customer = $this->context->customer; //id_customer = $params['cart']->id_customer;
        if( $customer->id ) {
            $customer_email = $customer->email;
            $processed_address = strtolower($customer_email); //conversion to lower case 
            $processed_address = trim($processed_address); //trimming
            $processed_address = mb_convert_encoding($processed_address, "UTF-8", mb_detect_encoding($customer_email)); //conversion from ISO-8859-1 to UTF-8 (replace "ISO-8859-1" by the source encoding of your string)
            $processed_address = md5($processed_address); //hash with MD5 algorithm
            $hashedEmail = $processed_address;
        }
        else
          $hashedEmail = '';  

        $this->context->smarty->assign("hashedEmail",$hashedEmail);
        return $this->display(__FILE__, 'views/templates/hooks/datalayer.tpl');
    }


    public function hookOrderConfirmation($params) {

        $obj_order = $params['objOrder'];
        $new_Cart = new Cart($params['objOrder']->id_cart);
        $customer = new Customer($new_Cart->id_customer);
        $order = new Order($obj_order->id_cart);
        //necesitamos transformar el objeto en array (la propiedad id_order está protegida)
        $order_as_array = get_object_vars($obj_order);

        //if first order, we have a new customer
        if( count(Order::getCustomerOrders($customer->id)) == 1 )
            $this->context->smarty->assign("type_of_customer", "new_customer");
        else
            $this->context->smarty->assign("type_of_customer", "returning_customer");

        
        if (Validate::isLoadedObject($obj_order)) {
            $order = get_object_vars($obj_order);
            $order_id = $order['id'];
            
            $this->context->smarty->assign("transactionId", $order_id); //Transaction ID
            $this->context->smarty->assign("transactionTotal", $order['total_paid']); //
            $this->context->smarty->assign("transactionShipping", $order['total_shipping']); //
            $products_in_cart = $new_Cart->getProducts(true);
            $this->context->smarty->assign("transactionProducts", $products_in_cart); //
            $this->context->smarty->assign("dataLayer", $order);
        }
    }

    //**********************************************//
    //          Formulario de configuración         //
    //**********************************************//

     public  function getContent()
    {
      $output = null;
    
        if (Tools::isSubmit('submit'.$this->name))
        {
            $gtm_id = strval(Tools::getValue('GTM_ID'));
            
            //comprobamos si ya se han subido los bonos
            if (!$gtm_id
              || empty($gtm_id)
              || !Validate::isGenericName($gtm_id))
                $output .= $this->displayError($this->l('Necesitas un ID de contenedor para que el módulo funcione'));
            else
            {
                Configuration::updateValue('GTM_ID', $gtm_id);
                $output .= $this->displayConfirmation($this->l('Id grabado'));
            }
        }
        
        
        return $output.$this->displayForm();
    }

    public function  displayForm() {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
        $input_array[] = array(
                    'type' => 'text',
                    'label' => $this->l('Id de contendor'),
                    'name' => 'GTM_ID',
                    'size' => 37,
                    'required' => true
                );
        
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Configuración'),
            ),
            'input' => $input_array,
            'submit' => array(
                'title' => $this->l('Guardar'),
                'class' => 'button'
            )
        );
     
        $helper = new HelperForm();
     
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
     
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
     
        // Load current value
        $helper->fields_value['GTM_ID'] = Configuration::get('GTM_ID');
     
        return $helper->generateForm($fields_form);
    }
}
