 ID Card Generator System

A complete PHP-based ID card generation system that matches your school's exact design and supports both single and bulk student ID card creation.

## 🌟 Features

- ✅ **Exact Design Match**: Replicates the Little Krish ID card design precisely
- 📊 **Bulk Upload**: Generate hundreds of ID cards from Excel/CSV files
- 👤 **Single Entry**: Create individual ID cards with a simple form
- 🖼️ **Auto Photo Processing**: Automatic cropping and resizing of student photos
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile devices
- 🖨️ **Print-Ready**: Optimized for professional printing with duplex support
- 🎨 **Professional Quality**: High-resolution output suitable for lamination

## 📋 Requirements

- PHP 7.4 or higher
- GD Library (for image processing)
- ZIP Extension (for bulk photo upload)
- Web server (Apache/Nginx)

## 🚀 Installation

1. **Upload Files**: Extract all files to your web server directory

2. **Set Permissions**:
```bash
chmod 755 uploads/
chmod 755 uploads/photos/
chmod 755 uploads/processed/
```

3. **Access the System**: Navigate to `http://your-domain.com/index.php`

## 📖 Usage Guide

### Single Student ID Card

1. Click on **"Single Student Entry"** section
2. Fill in the form:
   - Upload student photo (JPG/PNG)
   - Enter student name
   - Select date of birth
   - Enter blood group
   - Enter parent name
   - Enter phone number
   - Enter address
   - Select class (Todd Care, Nursery, Jr. KG, Sr. KG, Day Care)
3. Click **"Generate ID Card"**
4. Review both front and back sides
5. Click **"Print Cards"** to print

### Bulk Upload from Excel

#### Step 1: Prepare Excel File

Create an Excel/CSV file with these columns:

| fname | mob | add | bg |
|-------|-----|-----|-----|
| Manoj. M | 9880838396 9159446134 | No. 18, 5th Street, RB Avenue, Kamarajapuram, Chennai – 73. | AB+ve |
| V. Manoj Kumar | 9500987902 9894102427 | Block A S2, 2nd floor, Joel Enclave Apartment, Iyappan Nagar, 2nd street, Kamarajapuram, Chennai 73. | O+ve |

**Column Descriptions:**
- `fname`: Student name (Format: "FirstName LastName. ParentInitial")
- `mob`: Mobile numbers (space or comma separated)
- `add`: Complete address
- `bg`: Blood group (AB+ve, O+ve, B+ve, etc.)

#### Step 2: Prepare Photos

1. Name each photo file with the student's mobile number
   - Example: `9880838396.jpg`, `9500987902.png`
2. Create a ZIP file containing all photos
3. Supported formats: JPG, JPEG, PNG

#### Step 3: Upload and Generate

1. Click on **"Bulk Upload from Excel"** section
2. Upload your Excel/CSV file
3. Upload your photos ZIP file
4. Click **"Generate All ID Cards"**
5. Wait for processing (may take a few moments for large batches)
6. Review all generated cards
7. Use view toggles to switch between Front/Back/Both sides
8. Click **"Print All Cards"** for bulk printing

## 🖨️ Printing Instructions

### Single Card Printing

1. Click "Print Cards" button
2. Select your printer
3. For duplex: Print front, flip paper, print back
4. Cut to size: 340mm × 480mm
5. Laminate for durability

### Bulk Card Printing

1. Use view toggle buttons:
   - **Front Side**: Print all front sides first
   - **Back Side**: Print all back sides (after flipping)
   - **Both Sides**: For duplex printers
2. Recommended paper: A4 glossy photo paper or cardstock
3. Each card prints on a separate page
4. Cut and laminate after printing

## 🎨 Customization

### Modify School Information

Edit `id_card_template.php` to update:
- School name
- School address
- Contact information
- Logo/branding

### Change Card Design

The design uses Tailwind CSS classes. Modify colors, fonts, and layout in `id_card_template.php`.

### Adjust Photo Dimensions

Edit the `processImage()` function in `process_single.php` and `process_bulk.php`:

```php
$targetW = 300;  // Width in pixels
$targetH = 350;  // Height in pixels
```

## 📁 File Structure

```
├── index.php                 # Main entry point with upload forms
├── id_card_template.php      # ID card design template
├── process_single.php        # Single student processing
├── process_bulk.php          # Bulk upload processing
├── view_card.php            # Single card display
├── view_bulk.php            # Bulk cards display
├── uploads/                 # Photo storage directory
│   ├── photos/             # Extracted photos
│   └── processed/          # Processed/cropped photos
└── README.md               # This file
```

## 🔧 Troubleshooting

### Photos Not Displaying

- Check file permissions on `uploads/` directory
- Ensure photos are named correctly (matching mobile numbers)
- Verify image formats (JPG, PNG supported)

### Bulk Upload Fails

- Check Excel file format (CSV recommended)
- Ensure column names match exactly: `fname`, `mob`, `add`, `bg`
- Verify ZIP file is not corrupted
- Check PHP upload limits in `php.ini`:
  ```ini
  upload_max_filesize = 50M
  post_max_size = 50M
  ```

### Print Quality Issues

- Use high-quality photo paper
- Set printer to highest quality mode
- Ensure photos are at least 300x350 pixels
- Use 300 DPI or higher for printing

## 🎯 Tips for Best Results

1. **Photo Guidelines**:
   - Use passport-size photos with light background
   - Face should be clearly visible and centered
   - Minimum resolution: 300x350 pixels
   - Avoid dark or cluttered backgrounds

2. **Data Entry**:
   - Use uppercase for names for consistency
   - Verify all phone numbers are correct
   - Keep addresses concise but complete
   - Double-check blood group information

3. **Printing**:
   - Test print one card before bulk printing
   - Use lamination pouches (340mm × 480mm)
   - Allow ink to dry before handling
   - Store cards in protective sleeves

## 📞 Support

For technical support or customization requests:
- Email: littlekrishpreschool@gmail.com
- Website: www.littlekrishpreschool.com

## 📄 License

Copyright © 2025 Little Krish Montessori Pre-School. All rights reserved.

---

**Made with ❤️ for Little Krish Montessori Pre-School**
