# 🚀 QUICK START GUIDE

## Instant Setup (60 seconds)

### Step 1: Upload Files (15 seconds)
1. Download all files from this folder
2. Upload to your web server (public_html, www, or htdocs folder)
3. That's it! No database needed.

### Step 2: Set Permissions (10 seconds)
Run these commands via SSH or use File Manager:
```bash
chmod 755 uploads/
mkdir uploads/photos uploads/processed
chmod 755 uploads/photos uploads/processed
```

### Step 3: Access System (5 seconds)
Open your browser and go to:
```
http://your-domain.com/demo.php
```

### Step 4: Start Creating (30 seconds)
- **For Single Card**: Click "Start Creating ID Cards" → Fill form → Generate
- **For Bulk Cards**: Click "Download Sample Excel" → Prepare your data → Upload

---

## 📁 What You Got

| File | Purpose |
|------|---------|
| `demo.php` | Landing page with examples |
| `index.php` | Main system (upload forms) |
| `view_card.php` | Display single ID card |
| `view_bulk.php` | Display multiple ID cards |
| `id_card_template.php` | Design template |
| `process_single.php` | Handle single upload |
| `process_bulk.php` | Handle bulk upload |
| `sample_data.csv` | Example data file |
| `README.md` | Full documentation |

---

## 💡 First Time Usage

### Option A: Create One ID Card
1. Go to `index.php`
2. Fill the "Single Student Entry" form
3. Upload photo + enter details
4. Click "Generate ID Card"
5. Click "Print Cards"

### Option B: Create Multiple ID Cards
1. Prepare Excel/CSV with columns: `fname`, `mob`, `add`, `bg`
2. Name photos as mobile numbers (e.g., `9880838396.jpg`)
3. Zip all photos
4. Go to `index.php`
5. Upload Excel + ZIP file
6. Click "Generate All ID Cards"
7. Review and print

---

## 🎨 Customize Your Design

Edit `id_card_template.php` to change:
- **School Name**: Line 31, 130
- **School Address**: Line 32
- **Contact Info**: Line 33-36
- **Colors**: Search for color classes (e.g., `bg-teal-600`, `text-orange-600`)
- **Logo**: Replace SVG code at lines 26-31

---

## 📋 Excel Format Example

Create CSV/Excel with these exact column names:

```csv
fname,mob,add,bg
Manoj. M,9880838396,"No. 18, 5th Street, Chennai",AB+ve
Kumar. R,9500987902,"Block A, Joel Enclave, Chennai",O+ve
```

**Photo Naming**: Save as `9880838396.jpg`, `9500987902.jpg`, etc.

---

## 🖨️ Printing Tips

### For Best Results:
- Use **glossy photo paper** (A4 size)
- Set printer to **highest quality**
- Print **front sides first**, then flip and print **back sides**
- Laminate with **340mm × 480mm** pouches

### Duplex Printing:
1. Click "Front Side" button
2. Print all fronts
3. Flip papers (check orientation!)
4. Click "Back Side" button
5. Print all backs

---

## ⚡ Troubleshooting

**Photos not showing?**
→ Check uploads folder permissions (chmod 755)

**Can't upload large files?**
→ Edit php.ini: `upload_max_filesize = 50M`

**Bulk upload fails?**
→ Verify Excel columns: fname, mob, add, bg

**Print quality poor?**
→ Use 300 DPI minimum, glossy paper

---

## 🎯 Pro Tips

1. **Test First**: Generate one card before bulk processing
2. **Backup Data**: Keep original Excel + photos safe
3. **Photo Quality**: Use 300×350 minimum resolution
4. **Consistent Format**: Keep all names in UPPERCASE
5. **Verify Blood Groups**: Double-check accuracy

---

## 📞 Need Help?

- Check `README.md` for detailed documentation
- Sample data available in `sample_data.csv`
- All files are well-commented

---

## ✅ System Requirements

- PHP 7.4+ (with GD library)
- Web server (Apache/Nginx)
- 50MB disk space minimum
- Modern browser for viewing

---

**That's it! You're ready to generate professional ID cards! 🎉**

Start with `demo.php` to see examples, then use `index.php` to create your cards.