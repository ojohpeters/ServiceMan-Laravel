import React from 'react';

const StatsCard = ({ 
    title, 
    value, 
    change, 
    changeType = 'neutral', 
    icon, 
    color = 'blue',
    onClick 
}) => {
    const colorClasses = {
        blue: 'bg-blue-500 text-white',
        green: 'bg-green-500 text-white',
        yellow: 'bg-yellow-500 text-white',
        red: 'bg-red-500 text-white',
        purple: 'bg-purple-500 text-white',
        indigo: 'bg-indigo-500 text-white',
    };

    const changeClasses = {
        positive: 'text-green-600',
        negative: 'text-red-600',
        neutral: 'text-gray-600',
    };

    return (
        <div 
            className={`bg-white rounded-lg shadow-md p-6 cursor-pointer transition-transform hover:scale-105 ${onClick ? 'hover:shadow-lg' : ''}`}
            onClick={onClick}
        >
            <div className="flex items-center justify-between">
                <div className="flex-1">
                    <p className="text-sm font-medium text-gray-600 mb-1">{title}</p>
                    <p className="text-2xl font-bold text-gray-900">{value}</p>
                    {change !== undefined && (
                        <div className="flex items-center mt-2">
                            <span className={`text-sm font-medium ${changeClasses[changeType]}`}>
                                {change > 0 ? '+' : ''}{change}%
                            </span>
                            <span className="text-sm text-gray-500 ml-1">vs last month</span>
                        </div>
                    )}
                </div>
                <div className={`p-3 rounded-full ${colorClasses[color]}`}>
                    {icon}
                </div>
            </div>
        </div>
    );
};

export default StatsCard;
