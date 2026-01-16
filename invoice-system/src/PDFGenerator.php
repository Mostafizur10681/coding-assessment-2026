<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer packages
use Dompdf\Dompdf;

class PDFGenerator {

    /**
     * Generate PDF from an Invoice
     *
     * @param Invoice $invoice
     * @return void (PDF streamed to browser)
     */
    public static function generatePDF($invoice) {
        $dompdf = new Dompdf();

        // Build HTML table for items
        $itemsHtml = '';
        foreach ($invoice->getItems() as $item) {
            $qty = $item['qty'] ?? 0;
            $lineTotal = $item['price'] * $qty;
            $itemsHtml .= "<tr>
            <td>".htmlspecialchars($item['name'])."</td>
            <td>$".number_format($item['price'], 2)."</td>
            <td>$qty</td>
            <td>$".number_format($lineTotal, 2)."</td>
        </tr>";
        }

        $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            h1 { text-align: center; }
            p { font-size: 14px; }
        </style>
    </head>
    <body>
        <h1>Invoice #{$invoice->getId()}</h1>
        <p><strong>Customer:</strong> ".htmlspecialchars($invoice->getCustomer())."</p>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                $itemsHtml
            </tbody>
        </table>
        <p><strong>Total: $".number_format($invoice->getTotal(), 2)."</strong></p>
    </body>
    </html>
    ";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Invoice_'.$invoice->getId().'.pdf';

        // **Directly save PDF to file**
        file_put_contents($fileName, $dompdf->output());

        return $fileName; // Returns the path to generated PDF
    }


    /**
     * Optional: Export invoice as HTML file
     * Fallback if PDF generation is not desired
     *
     * @param Invoice $invoice
     * @return string Filename of generated HTML
     */
    public static function exportHTML($invoice) {
        $filename = 'invoice_' . $invoice->getId() . '.html';
        $html = "<h1>Invoice #{$invoice->getId()}</h1>";
        $html .= "<p>Customer: ".htmlspecialchars($invoice->getCustomer())."</p>";
        $html .= "<p>Total: $".number_format($invoice->getTotal(), 2)."</p>";
        file_put_contents($filename, $html);
        return $filename;
    }
}
