<?php

/**
 * Invoice Class
 *
 * Handles invoice creation and management
 * Started: 2 weeks ago
 * Last modified: Friday (was in a hurry)
 */
class Invoice {

    private $customer;
    private $items = [];
    private $discount = 0;
    private $id;
    private $createdAt;

    public function __construct($customerName) {
        $this->customer = $customerName;
        $this->id = time(); // Not sure if this is the best approach...
        $this->createdAt = date('Y-m-d H:i:s');
    }

    /**
     * Add an item to the invoice
     * Note: Make sure to use consistent naming!
     */
    public function addItem($name, $price, $quantity) {
        // Input validation
        if (empty($name)) {
            throw new Exception("Item name cannot be empty");
        }

        if (!is_numeric($price) || $price < 0) {
            throw new Exception("Price must be a non-negative number");
        }

        if (!is_int($quantity) || $quantity <= 0) {
            throw new Exception("Quantity must be a positive integer");
        }
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'qty' => $quantity  // Using 'qty' here
        ];
    }

    /**
     * Calculate total
     * BUG: This doesn't match up with addItem() - need to fix
     */
    public function getTotal() {
        $total = 0;
        foreach ($this->items as $item) {
            // Accessing 'quantity' but we stored it as 'qty'!
            $total += $item['price'] * $item['qty'];
        }
        return $total - $this->discount;
    }

    /**
     * Apply discount to invoice
     * TODO: Should discounts apply before or after tax?
     * TODO: Client hasn't decided on the business rules yet
     */
    public function applyDiscount($percent) {
        if (!is_numeric($percent) || $percent < 0 || $percent > 100) {
            throw new Exception("Discount percent must be between 0 and 100");
        }
        $subtotal = $this->getTotal();
        $this->discount = $subtotal * ($percent / 100);
    }

    /**
     * Get invoice ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get customer name
     */
    public function getCustomer() {
        return $this->customer;
    }

    /**
     * Get items array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Convert invoice to array for JSON serialization
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'items' => $this->items,
            'discount' => $this->discount,
            'total' => $this->getTotal(),
            'created_at' => $this->createdAt
        ];
    }

    /**
     * Save invoice to file
     * FIXME: This overwrites everything! Need to fix but running out of time
     * Should APPEND to the file, not replace it
     */
    public function saveToFile($filename = 'data/invoices.json') {
        $data = [];

        // Load existing invoices if file exists
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $data = json_decode($contents, true) ?: [];
        }

        // Append current invoice
        $data[] = $this->toArray();

        // Save back to file
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

        return true;
    }


    /**
     * Load invoice from file by ID
     * Started this but didn't finish testing it
     */
    public static function loadFromFile($id, $filename = 'data/invoices.json') {
        if (!file_exists($filename)) {
            throw new Exception("Invoice file not found");
        }

        $contents = file_get_contents($filename);
        $invoices = json_decode($contents, true) ?: [];

        foreach ($invoices as $invoiceData) {
            if ($invoiceData['id'] == $id) {
                $invoice = new Invoice($invoiceData['customer']);
                $invoice->id = $invoiceData['id'];
                $invoice->discount = $invoiceData['discount'];

                foreach ($invoiceData['items'] as $item) {
                    $qty = $item['qty'] ?$item['qty'] : 0;  // fix qty key
                    $invoice->addItem($item['name'], $item['price'], $qty);
                }

                return $invoice;
            }
        }

        throw new Exception("Invoice not found: " . $id);
    }
}
