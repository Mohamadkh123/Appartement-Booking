# Wallet System Implementation

## Overview
This document describes the implementation of an electronic wallet system for tenants and renters in the apartment booking platform. The system allows admins to deposit money into tenant wallets and withdraw money from renter wallets.

## Key Features

### 1. Wallet Management
- Each user (tenant or renter) has a wallet with a balance
- Admins can deposit money into tenant wallets
- Admins can withdraw money from renter wallets
- Users can view their wallet balance

### 2. Role-Based Access Control
- Only admins can deposit/withdraw funds
- Tenants can receive deposits
- Renters can have withdrawals
- All authenticated users can view their own wallet balance

### 3. Validation & Security
- Amount validation (minimum 0.01)
- Role validation (tenants for deposits, renters for withdrawals)
- Sufficient balance checking for withdrawals
- Proper error handling and response formatting

## Technical Implementation

### Database Structure
- **wallets table** with fields:
  - id (primary key)
  - user_id (foreign key to users table)
  - balance (decimal with 2 decimal places, default 0.00)
  - timestamps (created_at, updated_at)

### Models
- **Wallet Model**: Represents a user's wallet with balance and user relationship
- **User Model**: Updated to include wallet relationship

### Controllers
- **WalletController**: Handles all wallet-related operations
  - `deposit()`: Adds money to a tenant's wallet
  - `withdraw()`: Removes money from a renter's wallet
  - `balance()`: Retrieves a user's wallet balance

### Resources
- **UserResource**: Updated to include wallet_balance field

### Routes
- **Admin Routes** (require admin authentication):
  - `POST /api/admin/wallet/deposit/{user}` - Deposit to tenant wallet
  - `POST /api/admin/wallet/withdraw/{user}` - Withdraw from renter wallet
- **User Route** (requires authentication):
  - `GET /api/wallet/balance/{user}` - Get wallet balance

## API Endpoints

### Deposit to Tenant Wallet (Admin only)
```
POST /api/admin/wallet/deposit/{user_id}
```
Fields:
- amount (required, numeric, minimum 0.01)

### Withdraw from Renter Wallet (Admin only)
```
POST /api/admin/wallet/withdraw/{user_id}
```
Fields:
- amount (required, numeric, minimum 0.01)

### Get Wallet Balance
```
GET /api/wallet/balance/{user_id}
```

## Business Logic

### Deposit Process
1. Admin specifies tenant user ID and amount
2. System validates the user is a tenant
3. System creates wallet if it doesn't exist
4. System adds amount to wallet balance
5. Returns updated balance

### Withdrawal Process
1. Admin specifies renter user ID and amount
2. System validates the user is a renter
3. System checks for sufficient balance
4. System subtracts amount from wallet balance
5. Returns updated balance

### Balance Inquiry
1. User requests their wallet balance
2. System retrieves or creates wallet with zero balance
3. Returns current balance

## Error Handling
- Invalid user roles (non-tenant for deposits, non-renter for withdrawals)
- Insufficient balance for withdrawals
- Invalid amounts (negative or zero values)
- User not found

## Benefits
1. **Financial Tracking**: Enables tracking of financial transactions within the platform
2. **Role Separation**: Clearly separates tenant deposits from renter withdrawals
3. **Security**: Only admins can initiate financial transactions
4. **Transparency**: Users can view their wallet balances
5. **Extensibility**: System can be extended for more complex financial operations

## Future Enhancements
1. Transaction history tracking
2. Automated deposit/withdrawal based on booking completion
3. Integration with external payment gateways
4. Wallet transfer between users
5. Currency support for international users