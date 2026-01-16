<?php
require_once 'src/Invoice.php';
require_once 'src/PDFGenerator.php';

// Create invoice
$invoice = new Invoice("Customer CLI");
$invoice->addItem("Product A", 10, 2);
$invoice->addItem("Product B", 20, 1);

// Generate PDF (saved directly to file)
$pdfPath = PDFGenerator::generatePDF($invoice);

echo "PDF successfully generated: $pdfPath\n";
