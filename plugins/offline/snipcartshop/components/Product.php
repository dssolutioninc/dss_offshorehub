<?php namespace OFFLINE\SnipcartShop\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use OFFLINE\SnipcartShop\Classes\OrderCompleted;
use Flash;
use Carbon\Carbon;
use OFFLINE\SnipcartShop\Models\CurrencySettings;
use OFFLINE\SnipcartShop\Models\GeneralSettings;
use OFFLINE\SnipcartShop\Models\OrderItem as OrderItemModel;
use OFFLINE\SnipcartShop\Models\Order as OrderModel;
use OFFLINE\SnipcartShop\Models\Product as ProductModel;
use Mail;
use Input;
use Redirect;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Product extends ComponentBase
{
    use SetsVars;

    /**
     * The product to display.
     * @var ProductModel
     */
    public $product;
    /**
     * If the snipcart overlay will open automatically.
     * @var boolean
     */
    public $autoPop;
    /**
     * Reference to the page name for linking to products.
     * @var string
     */
    public $productPage;
    /**
     * Show custom fields directly on the product page.
     * @var boolean
     */
    public $displayCustomFields;

    public $name_tour_order;
    
    public function componentDetails()
    {
        return [
            'name'        => 'offline.snipcartshop::lang.components.product.details.name',
            'description' => 'offline.snipcartshop::lang.components.product.details.description',
        ];
    }

    public function defineProperties()
    {
        $langPrefixProduct  = 'offline.snipcartshop::lang.components.product.properties.';
        $langPrefixProducts = 'offline.snipcartshop::lang.components.products.properties.';

        return [
            'productSlug'         => [
                'title'       => $langPrefixProduct . 'productSlug.title',
                'description' => $langPrefixProduct . 'productSlug.description',
                'type'        => 'string',
                'default'     => '{{ :slug }}',
            ],
            'productPage'         => [
                'title'       => $langPrefixProducts . 'productPage.title',
                'description' => $langPrefixProducts . 'productPage.description',
                'type'        => 'dropdown',
                'default'     => '',
            ],
            'displayCustomFields' => [
                'title'       => $langPrefixProducts . 'displayCustomFields.title',
                'description' => $langPrefixProducts . 'displayCustomFields.description',
                'type'        => 'checkbox',
                'default'     => false,
            ],
        ];
    }

    public function getProductPageOptions()
    {
        return [null => '(' . trans('offline.snipcartshop::lang.plugin.common.use_backend_defaults') . ')']
            + Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }

    public function onRun()
    {
        try {
            $this->setVar('product', $this->loadProduct());
            $this->setVar('productPage', $this->getProductPage());
            $this->setVar('autoPop', GeneralSettings::get('auto_pop', true));
            $this->setVar('displayCustomFields', (bool)$this->property('displayCustomFields', false));
        } catch (NotFoundHttpException $e) {
            return Redirect::to('/404');
        }

        $this->setMetaData();
    }

    public function onAdd()
    {
        Flash::success(trans('offline.snipcartshop::lang.components.product.added_to_cart'));
    }
    
   public function onSend()
    {
       $data = post();
       $order = new OrderModel;
       $order->status = '0';
       $order->email = $data['email'];
       $order->billing_address = $data['address'];
       $order->date_from = $data['date_from'];
       $order->tour_name = $data['tour_name'];
       $order->status = 'processed';
       $order->creation_date = date('Y-m-d H:i:s');
       $invoice_number = 'HD' . $this->generateRandomString();
       $order->payment_method = $data['payment_method'];
       
       $order->invoice_number = $invoice_number;

       
       $prices = $data['display_price'];
       $quantity = $data['quantity'];
       $quantity_child = $data['children'];
       $quantity_huge = $data['childrenHuge'];
       $transportVal = $data['transportVal'];
       if($transportVal == null || empty($transportVal)){
           $transportVal = 0;
           $transport = 'Không có';
           $sumprice = $quantity * $prices + $quantity_child * ($prices*0.5) + $quantity_huge * ($prices*0.75);
       } else{
           $transportVal = $data['transportVal'];
           $transport = $data['transport'];
           $sumprice = $quantity * $prices + $quantity_child * ($prices*0.5) + $quantity_huge * ($prices*0.75) + $transportVal;
           
       }
      
       $order->grand_total = $sumprice;
       $order->save();
        
       $orderRes = $this->getOrderId($invoice_number);
       
       $orderItem = new OrderItemModel;
       $orderItem->order_id = $orderRes->id;
       $orderItem->product_id = $data['tourId'];
       
       $orderItem->quantity = $data['quantity'];
       $orderItem->quantity_tiny = $data['childrenTiny'];
       $orderItem->quantity_child = $data['children'];
       $orderItem->quantity_huge = $data['childrenHuge'];
        
       $orderItem->price = $prices;
        $orderItem->total_price = $sumprice;
        $orderItem->customer = $data['name'];
        $orderItem->email = $data['email'];
        $orderItem->address = $data['address'];
        $orderItem->date_from = $data['date_from'];
        $orderItem->mobile = $data['mobile'];
        $orderItem->save();
        
        // These variables are available inside the message as Twig
        $vars = ['name' => $data['name'],
            'tour_name' => $data['tour_name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'address' => $data['address'],
            'date_from' => $data['date_from'],
            'creation_date' => date('Y-m-d H:i:s'),
            'invoice_number' => $invoice_number,
            'payment_method' => $data['payment_method'],
            'price' => $data['display_price'],
            'audit' => $data['quantity'],
            'childrenTiny' => $data['childrenTiny'],
            'children' => $data['children'],
            'childrenHuge' => $data['childrenHuge'],
            'transport' => $transport,
            'transportVal' => $transportVal,
            'sumprice' => $sumprice
        ];
        //print_r($vars);
        $subject = "Start from: " . " " .$data['date_from']. " " . " - ". " " .$data['tour_name'];
        Mail::send('offline.snipcartshop::mail.message', $vars, function($message) use ($subject) {
            $message->to('itplusvn@gmail.com', 'Admin Person');
            $message->subject($subject);

        });
        // Handle Erros
        if (count(Mail::failures()) > 0) {
            echo "Failed to send Mail "; // Handle Failure
        } else {
            // Mail sent
            return true;
        }
    }

    public function onRecalculatePrice()
    {
        $price    = post('price');

        return format_money($price, $this->product);
    }

    protected function loadProduct()
    {
        $product = new ProductModel();

        $slug = $this->property('productSlug');

        $product = $product->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $product->transWhere('slug', $slug)
            : $product->where('slug', $slug);

        $product = $product->with([
            'main_image',
            'images',
            'downloads',
            'accessories',
            'custom_fields',
            'custom_fields.options',
        ])->first();

        if ( ! $product) {
            throw new NotFoundHttpException();
        }

        return $product;
    }

    protected function setMetaData()
    {
        $this->page->title = $this->product->meta_title
            ? $this->product->meta_title
            : $this->product->name;

        if ($this->product->meta_description) {
            $this->page->meta_description = $this->product->meta_description;
        }
    }

    private function getProductPage()
    {
        if ($this->property('productPage')) {
            return $this->property('productPage');
        }

        return GeneralSettings::get('product_page');
    }
    
    private function generateRandomString($length = 15) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
 private function getOrderId($invoice_number){
        $orderRes = OrderModel::where('invoice_number', $invoice_number)->first();
        return $orderRes;
    }
}