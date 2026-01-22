// Quick test to verify modal displays correctly
console.log('Testing modal display...');

// Test with sample data matching the API response structure
const testData = {
  success: true,
  data: [
    {
      channel_integration_id: '601d491c-f5fa-4488-b48b-f0ad2284f0e8',
      contact_full_name: 'Paijo',
      contact_phone_number: '6282243613621',
      created_at: '2025-12-31T18:13:34.814Z',
      id: '13264aa6-a484-47f3-bdd9-d795797d2be6',
      is_pacing: false,
      messages: {
        body: {
          type: 'body',
          template:
            'Pembayaran langganan internet Anda telah *berhasil…an ini dikirim otomatis oleh sistem *NBilling* ⚙️',
          parameters: {
            1: 'nama',
            2: 'tanggal',
            3: 'jumlah'
          }
        }
      },
      messages_broadcast_id: '7b5ab940-75c5-4344-9b4f-76eb2fbce7b8',
      messages_response: {
        read: {
          webhook: '898645283339202',
          statuses: [
            {
              id: 'wamid.HBgNNjI4MjI0MzYxMzYyMRUCABEYEjBCQjg0MUVEOTdGOUE3QTM2RQA=',
              status: 'read',
              timestamp: '1767247737'
            }
          ]
        }
      },
      status: 'read',
      whatsapp_error_message: 'n/a',
      whatsapp_message_id: 'wamid.HBgNNjI4MjI0MzYxMzYyMRUCABEYEjBCQjg0MUVEOTdGOUE3QTM2RQA='
    }
  ]
};

// Test the display function
if (typeof displayBroadcastDetail === 'function') {
  displayBroadcastDetail(testData);
  console.log('Test data sent to display function');
} else {
  console.error('displayBroadcastDetail function not found');
}
