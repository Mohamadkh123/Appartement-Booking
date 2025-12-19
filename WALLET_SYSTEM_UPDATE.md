# Wallet System Update Documentation

## Overview
This document describes the updated wallet system that allows renters to request withdrawals which are then processed by administrators.

## New Features

### 1. Withdrawal Requests
Renters can now submit withdrawal requests instead of having funds directly withdrawn by admins.

### 2. Admin Processing
Administrators can review, approve, or reject withdrawal requests from renters.

### 3. Request Tracking
All withdrawal requests are tracked with status (pending, approved, rejected).

## Database Changes

### New Table: withdrawal_requests
- `id` - Primary key
- `renter_id` - Foreign key to users table
- `amount` - Decimal amount requested
- `status` - Enum (pending, approved, rejected)
- `created_at` - Timestamp
- `updated_at` - Timestamp

## API Endpoints

### Renter Endpoints
- `POST /wallet/withdrawal-request` - Submit a withdrawal request
  - Requires: amount (minimum 0.01)

### Admin Endpoints
- `GET /admin/wallet/withdrawal-requests` - List all withdrawal requests
- `POST /admin/wallet/withdrawal-requests/{request_id}/approve` - Approve a withdrawal request
- `POST /admin/wallet/withdrawal-requests/{request_id}/reject` - Reject a withdrawal request

## Workflow

1. Renter submits withdrawal request through `/wallet/withdrawal-request`
2. System validates request and checks wallet balance
3. Request is stored with "pending" status
4. Admin reviews requests via `/admin/wallet/withdrawal-requests`
5. Admin approves or rejects the request
6. If approved, funds are deducted from renter's wallet
7. Request status is updated accordingly

## Security
- Only authenticated renters can submit requests
- Only administrators can approve/reject requests
- Balance validation prevents overdrafts
- Request status prevents double processing