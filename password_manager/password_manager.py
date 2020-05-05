import sqlite3
from hashlib import sha256

#setting admin password
admin_password = "123456"


def create_password(pass_key, service, admin_pass):
    #return set to 15 characters. Adjust to fit requirements for specific site.
    return sha256(admin_pass.encode('utf-8') + service.lower().encode('utf-8') + pass_key.encode('utf-8')).hexdigest()[:15]

def get_hex_key(admin_pass, service):
    return sha256(admin_pass.encode('utf-8') + service.lower().encode('utf-8')).hexdigest()

def get_password(admin_pass, service):
    secret_key = get_hex_key(admin_pass, service)
    cursor = conn.execute("SELECT * FROM keys WHERE pass_key=" + '"' + secret_key + '"')

    file_string = ""
    for row in cursor:
        file_string = row[0]
    return create_password(file_string, service, admin_pass)

def add_password(service, admin_pass):
    secret_key = get_hex_key(admin_pass, service)

    command = 'INSERT INTO keys (pass_key) VALUES (%s);' %('"' + secret_key + '"')
    conn.execute(command)
    conn.commit()
    return create_password(secret_key, service, admin_pass)

#main
#authentication
connect = input("What is your password?\n")

while connect != admin_password:
    connect = input("What is your password?\n")
    if connect == "q":
        break

#establishing sqlite connection
conn = sqlite3.connect('pass_manager.db')


if connect == admin_password:
    #try statement will only execute on first run to create database for password storage
    try:
        conn.execute('''CREATE TABLE keys
        (pass_key text PRIMARY KEY NOT NULL);''')
        print("Your safe has been created!\nWhat would you like to store in it today?")
    except:
        print("You have a safe, what would you like to do today?")

#getting and storing passwords
while True:
    print("\n" + "*" * 15)
    print("Commands:")
    print("q = quit program")
    print("gp = get password")
    print("sp = store password")
    print("*" * 15)
    user_input = input("Enter Choice: ")

    #quit program
    if user_input == "q":
        break
    #store password
    if user_input == "sp":
        service = input("What is the name of the service?\n")
        print("\n" + service.capitalize() + " password created:\n" + add_password(service, admin_password))
    #get password
    if user_input == "gp":
        service = input("What is the name of the service?\n")
        print("\n" + service.capitalize() + " password:\n" + get_password(admin_password, service))