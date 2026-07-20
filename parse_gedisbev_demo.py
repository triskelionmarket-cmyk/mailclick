import re
import ast
import json
import subprocess
import os

dump_path = "/Users/valentin_alucard/Downloads/gedisbev_nou2022.sql"
output_sql = "/tmp/gedisbev_import.sql"

print(f"Reading {dump_path}...")

# 1. Parse wpzy_posts for products and orders
products = {}
posts_content = []

with open(dump_path, "r", encoding="utf-8", errors="ignore") as f:
    for line in f:
        if "INSERT INTO `wpzy_posts`" in line or "INSERT INTO `wpzy_wc_customer_lookup`" in line or "INSERT INTO `wpzy_wc_order_stats`" in line or "INSERT INTO `wpzy_wc_order_product_lookup`" in line or "INSERT INTO `wpzy_postmeta`" in line:
            posts_content.append(line)

print(f"Captured {len(posts_content)} relevant INSERT lines")
