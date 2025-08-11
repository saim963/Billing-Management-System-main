<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('FPDF_FONTPATH', __DIR__ . '/../libs/font');
require('../libs/fpdf.php');

// Retrieve POST data with sanitization
$employ_id = isset($_POST['employ_id']) ? htmlspecialchars($_POST['employ_id']) : 'Unknown ID';
$name = isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : 'Unknown Name';
$designation = isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : 'Unknown Designation';
$status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : 'Unknown Status';
$department = isset($_POST['department']) ? htmlspecialchars($_POST['department']) : 'Unknown Department';
$contact = isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : 'Unknown Contact';
$address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : 'Unknown Address';
$bank = isset($_POST['bank_name']) ? htmlspecialchars($_POST['bank_name']) : 'Unknown Bank';
$account_no = isset($_POST['acc_number']) ? htmlspecialchars($_POST['acc_number']) : 'Unknown Account';
$ifsc_no = isset($_POST['ifsc_code']) ? htmlspecialchars($_POST['ifsc_code']) : 'Unknown IFSC';
$table_data = isset($_POST['table_data']) && is_array($_POST['table_data']) ? $_POST['table_data'] : [];
$sel_workplace = isset($_POST['selected_workplace']) ? htmlspecialchars($_POST['selected_workplace']) : 'Unknown Workplace';

// Filter table data based on selected workplace
$filtered_rows = [];
foreach($table_data as $row){
    if(isset($row['workplace']) && $row['workplace'] === $sel_workplace){
        $filtered_rows[] = $row;
    }
}

// Calculate total amount from table data
$total_amount = 0;
foreach ($filtered_rows as $row) {
    $total_amount += (int)$row['amount'];
}
// Teacher Welfare Fund: 10% of total_amount for permanent employees
$twf = ($status === 'Permanent') ? (int)(0.1 * $total_amount) : 0;
// Grand Total
$grand_total = $total_amount - $twf;

class PDF extends FPDF
{
    function Header()
    {
        // Add logo
        $this->Image('../res/amu_logo.png', 10, 6, 30);

        // Set font for title
        $this->SetFont('Times', 'B', 15);

        // First line: University name (centered)
        $this->Cell(0, 10, 'ALIGARH MUSLIM UNIVERSITY, ALIGARH', 0, 1, 'C');

        // Second line: Remuneration Bill (centered)
        $this->Cell(0, 10, 'REMUNERATION BILL', 0, 1, 'C');

        // Add some space after the header
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function BasicTable($header, $data, $sel_workplace)
    {
        // Set margins
        $margin = 10;
        $pageWidth = $this->GetPageWidth();
        $availableWidth = $pageWidth - (2 * $margin);

        // Table title with workplace
        $this->SetFont('Times', 'B', 14);
        $this->Cell($pageWidth, 7, "Table of service at $sel_workplace", 0, 1, 'L');

        // Header
        $this->SetFont('Times', 'B', 12);
        $cellWidth = $availableWidth / count($header);
        foreach ($header as $col) {
            $this->Cell($cellWidth, 7, $col, 1, 0, 'C');
        }
        $this->Ln();

        // Data
        $this->SetFont('Times', '', 12);
        foreach ($data as $row) {
            $this->Cell($cellWidth, 6, $row['course'], 1, 0, 'C');
            $this->Cell($cellWidth, 6, $row['date_from'], 1, 0, 'C');
            $this->Cell($cellWidth, 6, $row['date_to'], 1, 0, 'C');
            $this->Cell($cellWidth, 6, $row['job'], 1, 0, 'C');
            $this->Cell($cellWidth, 6, $row['candidates'], 1, 0, 'C');
            $this->Cell($cellWidth, 6, $row['amount'], 1, 0, 'C');
            $this->Ln();
        }
    }
}

// Instantiate the class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Set global margins to 10 mm on all sides
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

// Banking and Personal Details (side by side)
$pdf->SetFont('Times', 'B', 12);

// Place titles side by side
$margin = 10;
$halfWidth = ($pdf->GetPageWidth() - (2 * $margin)) / 2;

$pdf->Cell($halfWidth, 10, 'Personal Details', 0, 0, 'L');
$pdf->Cell($halfWidth - 65, 10, 'Banking Details', 0, 1, 'R');

// Personal and Banking Details side by side
$pdf->SetFont('Times', '', 12);

$personalDetails = [
    "ID No: $employ_id",
    "Name: $name",
    "Designation: $designation ($status)",
    "Department: $department",
    "Address of Department: $address",
    "Contact: $contact"
];

$bankingDetails = [
    "Bank: $bank",
    "Account Number: $account_no",
    "IFSC Number: $ifsc_no"
];

// Calculate the max number of rows
$maxRows = max(count($personalDetails), count($bankingDetails));

// Display details side by side
for ($i = 0; $i < $maxRows; $i++) {
    $pdf->SetX($margin);
    $personalDetail = isset($personalDetails[$i]) ? $personalDetails[$i] : '';
    $pdf->Cell($halfWidth, 6, $personalDetail, 0, 0, 'L');
    $bankingDetail = isset($bankingDetails[$i]) ? $bankingDetails[$i] : '';
    $pdf->Cell($halfWidth, 6, $bankingDetail, 0, 1, 'L');
}

// Add some space before the table
$pdf->Ln(10);

// Table for Work Done
$header = ['Course', 'Date From', 'Date To', 'Job', 'Candidates', 'Amount'];
$pdf->BasicTable($header, $filtered_rows, $sel_workplace);

// Display total amount, TWF, and grand total
$pdf->Ln(5);
$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 5, 'Total Amount: Rs ' . number_format($total_amount, 2), 0, 1, 'R');
$pdf->Cell(0, 5, 'To T.W.F. (10%): Rs ' . number_format($twf, 2), 0, 1, 'R');
$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(0, 5, 'Grand Total: Rs ' . number_format($grand_total, 2), 0, 1, 'R');

$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 5, "Verification by the Chairman/HOD/Principal of $sel_workplace", 0, 1, 'L');

// Signature Section
$pdf->Ln(25);
$pdf->SetFont('Times', '', 12);
$signatures = [
    "Claimant",
    "Head of Department",
    "Section Officer",
    "AFO / Deputy Controller",
];
$margin = 10;
$pageWidth = $pdf->GetPageWidth();
$availableWidth = $pageWidth - (2 * $margin);
$colWidth = $availableWidth / count($signatures);
foreach ($signatures as $sigTitle) {
    $pdf->Cell($colWidth, 10, '____________________', 0, 0, 'C');
}
$pdf->Ln(6);
foreach ($signatures as $sigTitle) {
    $pdf->Cell($colWidth, 10, $sigTitle, 0, 0, 'C');
}
$pdf->Ln();

// Output the PDF
$pdf->Output();
// $pdf->Output('D', 'remuneration_bill.pdf');
?>
