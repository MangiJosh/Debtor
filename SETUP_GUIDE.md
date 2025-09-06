# Quick Setup Guide - Email Integration

## ✅ **YES, it will work from the table!**

Your "Send Reminder" button in the table will now:
1. **Send professional email** via PHP backend
2. **Send WhatsApp message** with secure payment link
3. **Update the table** with today's date
4. **Show success/error messages**

## 🚀 **Quick Setup (5 minutes)**

### **Step 1: Setup PHP Backend**
```bash
# 1. Create database
mysql -u root -p < database_schema.sql

# 2. Install dependencies
composer install

# 3. Update configuration
cp config.php config_local.php
# Edit config_local.php with your database and email settings
```

### **Step 2: Update API Key**
In `debt_collection_demo.html`, find this line:
```javascript
'X-API-Key': 'your_secure_api_key_here'
```
Replace with your actual API key from `config_local.php`.

### **Step 3: Test It!**
1. Open your HTML file in browser
2. Go to Admin Dashboard
3. Click "Send Reminder" for any debtor
4. Check your email!

## 🔧 **What Happens When You Click "Send Reminder":**

### **Before (Old):**
- ❌ Just showed alert
- ❌ No real email sent
- ❌ No secure payment link

### **After (New):**
- ✅ **Sends real email** with professional template
- ✅ **Opens WhatsApp** with payment link
- ✅ **Updates table** with today's date
- ✅ **Shows success message** with payment link
- ✅ **Handles errors** gracefully

## 📧 **Email Features:**

### **Professional Email Template:**
- **Personalized greeting**: "Dear [Debtor Name]"
- **Account details**: Name, case ref, creditor
- **Amount due**: Correct amount for that debtor
- **Secure payment link**: Unique, time-limited URL
- **Security indicators**: Trust badges and verification
- **Legal compliance**: Required disclaimers

### **Security Features:**
- **Unique payment tokens**: Each link is different
- **Time-limited**: Links expire in 24 hours
- **API authentication**: Secure API key required
- **Rate limiting**: Prevents spam
- **Audit logging**: Track all activities

## 🎯 **How to Use:**

### **From Admin Table:**
1. Click "Send Reminder" button
2. Modal opens with debtor details
3. Click "Send Reminder" in modal
4. Email sent + WhatsApp opened
5. Table updates with today's date

### **From Three-Dots Menu:**
1. Click "⋯" next to any debtor
2. Select "View Page" or "Copy Link"
3. Opens debtor-specific page in new tab

## 🔍 **Troubleshooting:**

### **If emails don't send:**
1. Check database connection in `config_local.php`
2. Verify SMTP settings (Gmail, etc.)
3. Check API key matches in both files
4. Look at browser console for errors

### **If payment links don't work:**
1. Ensure `payment.php` exists (create this file)
2. Check database has payment_tokens table
3. Verify token generation in PHP

### **Test the API directly:**
Open `test_email_api.php` in browser to test the PHP backend.

## 📊 **Database Tables Created:**

- **debtors**: Your debtor information
- **payment_tokens**: Secure payment links
- **email_logs**: Track all sent emails
- **payment_history**: Payment records
- **security_logs**: Security events

## 🎉 **Result:**

Your debt collection system now has:
- ✅ **Real email sending**
- ✅ **Professional templates**
- ✅ **Secure payment links**
- ✅ **WhatsApp integration**
- ✅ **Database tracking**
- ✅ **Security features**
- ✅ **Error handling**

**The "Send Reminder" button in your table will work perfectly!** 🚀
