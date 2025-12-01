import React from 'react';

const SimpleChart = ({ 
    data, 
    title, 
    type = 'bar', 
    height = 200,
    color = 'blue'
}) => {
    if (!data || data.length === 0) {
        return (
            <div className="bg-white rounded-lg shadow-md p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">{title}</h3>
                <div className="flex items-center justify-center h-48 text-gray-500">
                    No data available
                </div>
            </div>
        );
    }

    const maxValue = Math.max(...data.map(item => item.value));

    const renderBarChart = () => (
        <div className="space-y-2">
            {data.map((item, index) => (
                <div key={index} className="flex items-center">
                    <div className="w-20 text-sm text-gray-600 truncate">
                        {item.label}
                    </div>
                    <div className="flex-1 mx-3">
                        <div className="bg-gray-200 rounded-full h-4">
                            <div
                                className={`bg-${color}-500 h-4 rounded-full transition-all duration-500`}
                                style={{ width: `${(item.value / maxValue) * 100}%` }}
                            />
                        </div>
                    </div>
                    <div className="w-12 text-sm text-gray-600 text-right">
                        {item.value}
                    </div>
                </div>
            ))}
        </div>
    );

    const renderLineChart = () => (
        <div className="relative h-48">
            <svg width="100%" height="100%" className="overflow-visible">
                <polyline
                    fill="none"
                    stroke={`rgb(${color === 'blue' ? '59, 130, 246' : color === 'green' ? '34, 197, 94' : '168, 85, 247'})`}
                    strokeWidth="2"
                    points={data.map((item, index) => {
                        const x = (index / (data.length - 1)) * 100;
                        const y = 100 - (item.value / maxValue) * 80;
                        return `${x},${y}`;
                    }).join(' ')}
                />
                {data.map((item, index) => {
                    const x = (index / (data.length - 1)) * 100;
                    const y = 100 - (item.value / maxValue) * 80;
                    return (
                        <circle
                            key={index}
                            cx={x}
                            cy={y}
                            r="4"
                            fill={`rgb(${color === 'blue' ? '59, 130, 246' : color === 'green' ? '34, 197, 94' : '168, 85, 247'})`}
                        />
                    );
                })}
            </svg>
        </div>
    );

    const renderPieChart = () => {
        const total = data.reduce((sum, item) => sum + item.value, 0);
        let cumulativePercentage = 0;

        return (
            <div className="flex items-center justify-center">
                <svg width="200" height="200" className="transform -rotate-90">
                    {data.map((item, index) => {
                        const percentage = (item.value / total) * 100;
                        const startAngle = cumulativePercentage * 3.6;
                        const endAngle = (cumulativePercentage + percentage) * 3.6;
                        
                        cumulativePercentage += percentage;

                        const radius = 80;
                        const x1 = 100 + radius * Math.cos((startAngle * Math.PI) / 180);
                        const y1 = 100 + radius * Math.sin((startAngle * Math.PI) / 180);
                        const x2 = 100 + radius * Math.cos((endAngle * Math.PI) / 180);
                        const y2 = 100 + radius * Math.sin((endAngle * Math.PI) / 180);

                        const largeArcFlag = percentage > 50 ? 1 : 0;

                        const pathData = [
                            `M 100 100`,
                            `L ${x1} ${y1}`,
                            `A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2} ${y2}`,
                            `Z`
                        ].join(' ');

                        return (
                            <path
                                key={index}
                                d={pathData}
                                fill={`hsl(${index * 60}, 70%, 50%)`}
                            />
                        );
                    })}
                </svg>
                <div className="ml-4">
                    {data.map((item, index) => (
                        <div key={index} className="flex items-center mb-2">
                            <div 
                                className="w-3 h-3 rounded-full mr-2"
                                style={{ backgroundColor: `hsl(${index * 60}, 70%, 50%)` }}
                            />
                            <span className="text-sm text-gray-600">{item.label}</span>
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <div className="bg-white rounded-lg shadow-md p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">{title}</h3>
            {type === 'bar' && renderBarChart()}
            {type === 'line' && renderLineChart()}
            {type === 'pie' && renderPieChart()}
        </div>
    );
};

export default SimpleChart;
