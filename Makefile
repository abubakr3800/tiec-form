.PHONY: help install dev build test clean docker-up docker-down

help: ## عرض المساعدة
	@echo "الأوامر المتاحة:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## تثبيت التبعيات
	@echo "تثبيت تبعيات PHP..."
	composer install --no-dev --optimize-autoloader
	@echo "تثبيت تبعيات Node.js..."
	npm install

dev: ## تشغيل بيئة التطوير
	@echo "تشغيل بيئة التطوير..."
	npm run dev

build: ## بناء المشروع للإنتاج
	@echo "بناء المشروع..."
	npm run build
	composer install --no-dev --optimize-autoloader

test: ## تشغيل الاختبارات
	@echo "تشغيل اختبارات PHP..."
	composer test
	@echo "تشغيل اختبارات JavaScript..."
	npm test

clean: ## تنظيف الملفات المؤقتة
	@echo "تنظيف الملفات المؤقتة..."
	rm -rf node_modules
	rm -rf vendor
	rm -rf dist
	rm -rf coverage
	rm -rf .nyc_output
	rm -rf .parcel-cache

docker-up: ## تشغيل Docker Compose
	@echo "تشغيل Docker Compose..."
	docker-compose up -d

docker-down: ## إيقاف Docker Compose
	@echo "إيقاف Docker Compose..."
	docker-compose down

docker-build: ## بناء Docker image
	@echo "بناء Docker image..."
	docker build -t tiec-registration-system .

docker-run: ## تشغيل Docker container
	@echo "تشغيل Docker container..."
	docker run -p 8080:80 tiec-registration-system

setup: ## إعداد المشروع بالكامل
	@echo "إعداد المشروع..."
	make install
	make docker-up
	@echo "المشروع جاهز! يمكنك الوصول له على http://localhost:8080"

deploy: ## نشر المشروع
	@echo "نشر المشروع..."
	make build
	make docker-build
	@echo "تم نشر المشروع بنجاح!" 