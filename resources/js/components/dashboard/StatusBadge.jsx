import React from 'react';

const StatusBadge = ({ status, size = 'sm' }) => {
    const statusConfig = {
        'PENDING_ADMIN_ASSIGNMENT': {
            color: 'bg-yellow-100 text-yellow-800',
            text: 'Pending Assignment'
        },
        'ASSIGNED_TO_SERVICEMAN': {
            color: 'bg-blue-100 text-blue-800',
            text: 'Assigned'
        },
        'SERVICEMAN_INSPECTED': {
            color: 'bg-purple-100 text-purple-800',
            text: 'Inspected'
        },
        'AWAITING_CLIENT_APPROVAL': {
            color: 'bg-orange-100 text-orange-800',
            text: 'Awaiting Approval'
        },
        'NEGOTIATING': {
            color: 'bg-indigo-100 text-indigo-800',
            text: 'Negotiating'
        },
        'AWAITING_PAYMENT': {
            color: 'bg-red-100 text-red-800',
            text: 'Awaiting Payment'
        },
        'PAYMENT_CONFIRMED': {
            color: 'bg-green-100 text-green-800',
            text: 'Payment Confirmed'
        },
        'IN_PROGRESS': {
            color: 'bg-blue-100 text-blue-800',
            text: 'In Progress'
        },
        'COMPLETED': {
            color: 'bg-green-100 text-green-800',
            text: 'Completed'
        },
        'CANCELLED': {
            color: 'bg-gray-100 text-gray-800',
            text: 'Cancelled'
        },
        'PENDING': {
            color: 'bg-yellow-100 text-yellow-800',
            text: 'Pending'
        },
        'SUCCESSFUL': {
            color: 'bg-green-100 text-green-800',
            text: 'Successful'
        },
        'FAILED': {
            color: 'bg-red-100 text-red-800',
            text: 'Failed'
        }
    };

    const config = statusConfig[status] || {
        color: 'bg-gray-100 text-gray-800',
        text: status
    };

    const sizeClasses = {
        sm: 'px-2 py-1 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-4 py-2 text-base'
    };

    return (
        <span className={`inline-flex items-center rounded-full font-medium ${config.color} ${sizeClasses[size]}`}>
            {config.text}
        </span>
    );
};

export default StatusBadge;
