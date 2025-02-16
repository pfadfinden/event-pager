import os
import socket
import sys
import time

BIND_ON_ADDRESS = os.environ.get('BIND_ON_ADDRESS', '0.0.0.0')
BIND_ON_PORT = os.environ.get('BIND_ON_PORT', 6000)
TIME_BETWEEN_MSG = os.environ.get('TIME_BETWEEN_MSG', 2)

# Create a TCP/IP socket
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
# Bind the socket to the port
server_address = (BIND_ON_ADDRESS, BIND_ON_PORT)
print('starting up on %s port %s' % server_address)
sock.bind(server_address)

def readData(connection, clientAddress):
  message = ''
  try:
    print('connection from' + str(clientAddress))
          # Receive the data in small chunks and retransmit it
    while True:
      data = connection.recv(1024).decode()
      if data:
        message = message + str(data)
      else:
        break
              
  finally:
    messageParts = message.split('\r')

    if len(messageParts) != 4:
        print('Message with invalid format: ' + repr(message))
    elif not messageParts[0].isdigit():
        print('Message with invalid cap: ' + messageParts[0])
    elif messageParts[2] != '' or messageParts[3] != '':
        print('Message with invalid ending: ' + repr(message))
    else:
        print('Message to cap code ' + messageParts[0] + ': ' + messageParts[1])

    # Sleep for 2 second. Simulating the real Pager-Transmitter.
    time.sleep(TIME_BETWEEN_MSG)
    # Clean up the connection
    connection.close()


# Listen for incoming connections
sock.listen(1)

while True:
  # Wait for a connection
  connection, clientAddress = sock.accept()
  readData(connection, clientAddress)
