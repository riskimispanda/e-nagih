function displayBroadcastDetail(data) {
  const content = document.getElementById('broadcastDetailContent');

  console.log('Broadcast detail data:', data);

  // Handle the actual Qontak WhatsApp broadcast API response structure
  const messageLogs = data.data || data;

  // Extract message status counts from message responses
  const statusCount = {};
  if (Array.isArray(messageLogs)) {
    messageLogs.forEach(log => {
      if (log.messages_response) {
        Object.keys(log.messages_response).forEach(status => {
          if (status !== 'contacts' && status !== 'messages' && status !== 'messaging_product') {
            statusCount[status] = (statusCount[status] || 0) + 1;
          }
        });
      }
    });
  }

  let messageLogsHtml = '';
  if (Array.isArray(messageLogs) && messageLogs.length > 0) {
    messageLogsHtml = messageLogs
      .map((log, index) => {
        return `
                <!-- Message ${index + 1} -->
                <div class="border-l-4 border-blue-400 pl-4 ${index > 0 ? 'mt-6' : ''}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Contact Information</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Name:</dt>
                                    <dd class="text-sm text-gray-900">${log.contact_full_name || 'N/A'}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Phone:</dt>
                                    <dd class="text-sm text-gray-900 font-mono">${log.contact_phone_number || 'N/A'}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Status:</dt>
                                    <dd><span class="status-badge status-${(log.status || 'pending').toLowerCase()}">${log.status || 'pending'}</span></dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">WhatsApp ID:</dt>
                                    <dd class="text-sm text-gray-900 font-mono text-xs">${log.whatsapp_message_id || 'N/A'}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Created:</dt>
                                    <dd class="text-sm text-gray-900">${log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A'}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Message Timeline</h4>
                            <dl class="space-y-2">
                                ${
                                  log.messages_response
                                    ? Object.entries(log.messages_response)
                                        .map(([statusType, statusData]) => {
                                          if (
                                            statusType === 'contacts' ||
                                            statusType === 'messages' ||
                                            statusType === 'messaging_product'
                                          )
                                            return '';

                                          const status = statusData.statuses && statusData.statuses[0];
                                          return `
                                        <div class="flex justify-between items-center">
                                            <dt class="text-sm font-medium text-gray-500 capitalize">${statusType}:</dt>
                                            <dd class="text-sm text-gray-900">
                                                <span class="status-badge status-${statusType}">${statusType}</span>
                                                ${status ? `<span class="ml-2 text-xs text-gray-600">${new Date(parseInt(status.timestamp) * 1000).toLocaleString()}</span>` : ''}
                                            </dd>
                                        </div>
                                    `;
                                        })
                                        .join('')
                                    : '<div class="text-sm text-gray-500">No status timeline available</div>'
                                }
                            </dl>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="mt-4 bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Message Content</h4>
                        <div class="space-y-4">
                            ${
                              log.messages && log.messages.body
                                ? `
                                <div>
                                    <h5 class="text-xs font-medium text-gray-700 mb-2">Template:</h5>
                                    <p class="text-sm text-gray-900 bg-white p-3 rounded border">${log.messages.body.template || 'N/A'}</p>
                                </div>
                                
                                ${
                                  log.messages.body.parameters
                                    ? `
                                    <div>
                                        <h5 class="text-xs font-medium text-gray-700 mb-2">Parameters:</h5>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    ${Object.entries(log.messages.body.parameters)
                                                      .map(
                                                        ([key, value]) => `
                                                        <tr>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">${key}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${value}</td>
                                                        </tr>
                                                    `
                                                      )
                                                      .join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `
                                    : ''
                                }
                            `
                                : '<div class="text-sm text-gray-500">No message content available</div>'
                            }
                        </div>
                    </div>

                    <!-- Error Information -->
                    ${
                      log.whatsapp_error_message && log.whatsapp_error_message !== 'n/a'
                        ? `
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-red-900 mb-2">Error Information</h4>
                            <p class="text-sm text-red-700">${log.whatsapp_error_message}</p>
                        </div>
                    `
                        : ''
                    }
                </div>
            `;
      })
      .join('');
  } else {
    messageLogsHtml = '<div class="text-center py-8 text-gray-500">No message logs available for this broadcast</div>';
  }

  const statusSummaryHtml = `
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Status Summary</h4>
            <dl class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500">Sent</dt>
                    <dd class="text-xl font-semibold text-blue-600">${statusCount.sent || 0}</dd>
                </div>
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500">Delivered</dt>
                    <dd class="text-xl font-semibold text-green-600">${statusCount.delivered || 0}</dd>
                </div>
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500">Read</dt>
                    <dd class="text-xl font-semibold text-purple-600">${statusCount.read || 0}</dd>
                </div>
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500">Pending</dt>
                    <dd class="text-xl font-semibold text-yellow-600">${statusCount.pending || 0}</dd>
                </div>
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500">Failed</dt>
                    <dd class="text-xl font-semibold text-red-600">${statusCount.failed || 0}</dd>
                </div>
            </dl>
        </div>
    `;

  content.innerHTML = `
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-soft p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Broadcast Log Details</h3>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        ${Array.isArray(messageLogs) ? messageLogs.length : 0} Message(s)
                    </span>
                </div>
            </div>

            ${messageLogsHtml}
            ${statusSummaryHtml}
        </div>
    `;
}
