# 🚀 Instance Monitor Altschool Project-based Exam

## 📘 Overview  
This repository shows a hands-on deployment of a simple web page on AWS using two hosting approaches:

Compute-based: 2 × EC2 instances running NGINX, fronted by an Application Load Balancer (ALB).

Storage-based: the same HTML file hosted as a Static Website on S3.

The page displays the instance IP dynamically (via Ansible templating). This project demonstrates provisioning, configuration management, automation, and a short comparison between EC2+ALB and S3 static hosting.

It showcases skills in **cloud infrastructure setup, configuration management, automation, and documentation**, using **Ansible** and **AWS services** to achieve an end-to-end DevOps deployment.  

---

## 🎯 Project Objectives  
| Task | Description | Weight |
|------|--------------|--------|
| **1. EC2 Setup** | Launch two EC2 instances on AWS (Free Tier). | 10% |
| **2. HTML Page** | Create or clone a simple HTML page that displays the instance’s IP address dynamically. | 15% |
| **3. Ansible Automation** | Use Ansible to install NGINX, enable it on boot, and deploy the HTML page on both EC2 instances. | 25% |
| **4. Load Balancer Setup** | Configure an Application Load Balancer (ALB) to distribute traffic between the EC2 instances. | 20% |
| **5. S3 Static Website** | Deploy the same HTML file to an S3 bucket and enable static website hosting. Compare this setup with the EC2+ALB configuration. | 15% |
| **6. Documentation** | Document the full process in a Medium article (no plagiarism). | 15% |

---

## 🧰 Tech Stack  
- **Cloud Provider:** AWS  
- **Compute:** EC2  
- **Load Balancing:** Application Load Balancer (ALB)  
- **Storage:** S3 (Static Website Hosting)  
- **Configuration Management:** Ansible  
- **Web Server:** NGINX  
- **Languages:** HTML, Shell, YAML  

---

## 🏗️ Architecture Overview  

add image of architecture


---

⚙️ Setup Instructions
1. Launch EC2 Instances

Sign in to the AWS Console.

Launch two EC2 instances (Amazon Linux 2 / Ubuntu — Free Tier).

Create / attach a key pair for SSH access.

Configure a Security Group: allow SSH (22) from your IP and HTTP (80) from anywhere (or as required).

Note the public DNS / IP addresses of both instances.

2. HTML Template (dynamic IP)

Create a Jinja2 template (index.html.j2) that Ansible will populate with the instance IP:

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>EC2 Instance Info</title>
  </head>
  <body>
    <h2>Welcome to my EC2 Instance!</h2>
    <p>Private IP: <strong>{{ ansible_default_ipv4.address }}</strong></p>
  </body>
</html>


Note: ansible_default_ipv4.address is provided by Ansible facts gathered on the target host.

3. Ansible: inventory & playbook

Sample inventory (inventory.ini):

[webservers]
ec2-1-public-dns ansible_host=ec2-1-public-ip ansible_user=ubuntu ansible_ssh_private_key_file=~/.ssh/mykey.pem
ec2-2-public-dns ansible_host=ec2-2-public-ip ansible_user=ubuntu ansible_ssh_private_key_file=~/.ssh/mykey.pem


Playbook (deploy.yml):

- hosts: webservers
  become: yes
  gather_facts: yes
  vars:
    www_root: /var/www/html

  tasks:
    - name: Install NGINX (Debian/Ubuntu)
      apt:
        name: nginx
        state: present
        update_cache: yes
      when: ansible_os_family == 'Debian'

    - name: Install NGINX (RedHat/CentOS/Amazon)
      yum:
        name: nginx
        state: present
      when: ansible_os_family == 'RedHat'

    - name: Ensure nginx is enabled and started
      service:
        name: nginx
        enabled: yes
        state: started

    - name: Create www root (if missing)
      file:
        path: "{{ www_root }}"
        state: directory
        owner: root
        group: root
        mode: '0755'

    - name: Deploy index.html from template
      template:
        src: index.html.j2
        dest: "{{ www_root }}/index.html"
        mode: '0644'


Run the playbook:

ansible-playbook -i inventory.ini deploy.yml


Make sure your local machine has network access (SSH) to the EC2 instances and Ansible can connect using the key file.

4. Application Load Balancer (ALB)

In AWS Console → EC2 → Load Balancers → Create Load Balancer → Application Load Balancer.

Choose appropriate VPC & subnets.

Configure a listener on HTTP:80.

Create a Target Group (target type: instance), register both EC2 instances.

Configure health checks (e.g., path /index.html or /).

Finish creation and note the ALB DNS name. Visit the ALB DNS to see the website (requests will be routed to EC2 instances).

5. S3 Static Website Hosting

Create an S3 bucket with a globally unique name.

Upload index.html (rendered file) to the bucket. Example with AWS CLI:

aws s3 mb s3://my-unique-bucket --region us-east-1
aws s3 cp index.html s3://my-unique-bucket/index.html --acl public-read
aws s3 website s3://my-unique-bucket/ --index-document index.html


Make the object (and bucket policy) publicly readable. Example bucket policy (replace my-unique-bucket):

{
  "Version":"2012-10-17",
  "Statement":[{
    "Sid":"PublicReadGetObject",
    "Effect":"Allow",
    "Principal": "*",
    "Action":["s3:GetObject"],
    "Resource":["arn:aws:s3:::my-unique-bucket/*"]
  }]
}


Visit the S3 website endpoint from the bucket properties and compare response time and behavior with the ALB endpoint.

🧠 Quick Comparison: EC2 + ALB vs S3 Hosting
Feature	EC2 + ALB	S3 Static Website
Hosting type	Compute-based	Storage-based
Scalability	Manual / Auto Scaling	Automatic (object storage)
Cost	Higher (instance runtime)	Lower (storage + requests)
Maintenance	OS & server updates required	Minimal
Use cases	Dynamic apps, server-side logic	Static content, landing pages, docs
📁 Suggested Repo Structure
.
├── README.md
├── inventory.ini
├── deploy.yml
├── index.html.j2
├── index.html            # optional: rendered file for S3 upload
└── docs/
    └── medium-article.md

📝 Documentation

Write a Medium article to document:

Design decisions and steps followed

Ansible playbook explanation

ALB configuration and health checks

S3 static hosting setup and bucket policy

A short comparison of costs, scalability, and maintenance

Link to your Medium article can be added here once published.

📚 Learning Outcomes

Provision EC2 instances and configure security groups

Automate server provisioning and deployment using Ansible

Configure NGINX to host a static page and enable it on boot

Set up an Application Load Balancer (ALB) and target groups

Host a static website on S3 and compare hosting approaches

Produce clear technical documentation for reproducibility

👨‍💻 Author

Nsikakobong Ubom
Cloud & DevOps Enthusiast | Application & Technology Manager

