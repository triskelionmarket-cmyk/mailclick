import re

sql_path = '/Users/valentin_alucard/Downloads/gedisbev_nou2022.sql'
print("Parsing multi-line mysqldump GedisBev SQL file...")

products = {}
customers = {}
current_table = None

with open(sql_path, 'r', encoding='utf-8', errors='ignore') as f:
    for line in f:
        if 'INSERT INTO `' in line:
            match = re.search(r'INSERT INTO `([^`]+)`', line)
            if match:
                current_table = match.group(1)
            continue

        if current_table == 'wpzy_woocommerce_order_items':
            # parse order item names
            for part in line.split('),('):
                sub = part.split(',')
                if len(sub) >= 3:
                    item_name = sub[1].strip(" ('\"\t\r\n")
                    item_type = sub[2].strip(" ('\"\t\r\n") if len(sub) > 2 else ""
                    if 'line_item' in item_type and len(item_name) > 3 and not item_name.startswith('Shipping'):
                        products[item_name] = products.get(item_name, 0) + 1

        elif current_table == 'wpzy_wc_customer_lookup':
            for part in line.split('),('):
                sub = part.split(',')
                if len(sub) >= 6:
                    try:
                        cid = int(sub[0].strip(" ('\"VALUES\t\r\n"))
                        fname = sub[3].strip(" ('\"\t\r\n")
                        lname = sub[4].strip(" ('\"\t\r\n")
                        email = sub[5].strip(" ('\"\t\r\n")
                        city = sub[7].strip(" ('\"\t\r\n") if len(sub) > 7 else 'București'
                        if email and '@' in email:
                            customers[cid] = {'first_name': fname, 'last_name': lname, 'email': email, 'city': city}
                    except Exception:
                        pass

print(f"Extracted {len(products)} product titles and {len(customers)} customers!")
print("Top 20 Products:")
sorted_prods = sorted(products.items(), key=lambda x: x[1], reverse=True)[:25]
for name, cnt in sorted_prods:
    print(f"  - {name} ({cnt} order items)")

print("\nSample Customers:")
for cid, c in list(customers.items())[:10]:
    print(f"  - {c['first_name']} {c['last_name']} ({c['email']}, {c['city']})")
