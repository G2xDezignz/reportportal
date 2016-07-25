# reportportal
A custom web app for created for a company who wanted a way for their Asset Management team to provide reports to Investors. 

In short, the app requires login credentials; managers can organize and upload reports and investors can access reports available to them. A model overview (ReportingPortal_Model.pdf) is provided in the _tmp folder. 

The app was developed with Dreamweaver (initially CS6; modified with CC 2015) in PHP 5+ and MySQL 5.7. A start SQL file (empty_DB.sql) is provided in the _tmp folder to create a new, basic database with a default user/password "admin/admin". 

Role-based access summary:
- Admins maintain users, company names, and report categories. New reports added by admins are automatically available.
- Asset Managers can add/edit/delete user accounts of Investors only; can view all reports and add new reports. New reports added are marked as 'Pending' to be approved by admins.
- Accounting can view all reports and add new reports. New reports added are marked as 'Pending' to be approved by admins.
- Executives can view all reports. 
- Investors can only view assigned reports.
