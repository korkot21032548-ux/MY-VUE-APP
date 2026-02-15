<template>
    <div class="container mt-4">
      <h2 class="mb-3">รายการพนักงาน</h2>
  
      <div class="mb-3">
        <button class="btn btn-primary" @click="openAddModal">เพิ่มพนักงาน+</button>
      </div>
  
      <table class="table table-bordered table-striped">
        <thead class="table-primary">
          <tr>
            <th>รหัสพนักงาน</th>
            <th>ชื่อ-นามสกุล</th>
            <th>แผนก</th>
            <th>เงินเดือน</th>
            <th>สถานะ</th>
            <th>รูปภาพ</th>
            <th>การจัดการ</th>
          </tr>
        </thead>
        <tbody>
    <tr v-for="emp in employees" :key="emp.emp_id">
      <td>{{ emp.emp_id }}</td>
      <td>{{ emp.full_name }}</td>
      <td>{{ emp.department }}</td>
      <td>{{ emp.salary }}</td>
  
      <td>
        <span v-if="emp.active == 1" class="badge bg-success">ปกติ</span>
        <span v-else class="badge bg-danger">ลาออก</span>
      </td>
  
      <td>
        <img
          v-if="emp.image"
          :src="'http://localhost/MY-VUE-APP/php_api/uploads/' + emp.image"
          width="60"
          class="rounded shadow-sm"
        />
        <span v-else class="text-muted">ไม่มีรูป</span>
      </td>
  
      <td>
        <button class="btn btn-warning btn-sm me-2" @click="openEditModal(emp)">
          แก้ไข
        </button>
        <button class="btn btn-danger btn-sm" @click="deleteEmployee(emp.emp_id)">
          ลบ
        </button>
      </td>
    </tr>
  </tbody>
      </table>
  
      <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
  
      <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditMode ? "แก้ไขข้อมูลพนักงาน" : "เพิ่มพนักงานใหม่" }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="saveEmployee">
               
                <div class="mb-3">
                  <label class="form-label">ชื่อ-นามสกุล</label>
                  <input v-model="editForm.full_name" type="text" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">แผนก</label>
                  <input v-model="editForm.department" type="text" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">เงินเดือน</label>
                  <input v-model="editForm.salary" type="number" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">สถานะ</label>
                  <select v-model="editForm.active" class="form-select">
                    <option value="1">ปกติ</option>
                    <option value="0">ลาออก</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">รูปภาพพนักงาน</label>
                  <input type="file" @change="handleFileUpload" class="form-control" :required="!isEditMode" />
                  <div v-if="isEditMode && editForm.image" class="mt-2">
                    <small class="text-muted d-block">รูปปัจจุบัน:</small>
                    <img :src="'http://localhost/MY-VUE-APP/php_api/uploads/' + editForm.image" width="100" class="mt-1 rounded" />
                  </div>
                </div>
                <div class="modal-footer px-0 pb-0">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn btn-success">
                    {{ isEditMode ? "บันทึกการแก้ไข" : "บันทึกข้อมูล" }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from "vue";
  
  export default {
    name: "EmployeeList",
    setup() {
      const employees = ref([]);
      const loading = ref(true);
      const error = ref(null);
      const isEditMode = ref(false);
      const editForm = ref({
        emp_id:   null,
        full_name: "",
        department: "",
        salary: "",
        active: 1,
        image: ""
      });
      const newImageFile = ref(null);
      let modalInstance = null;
  
      // โหลดข้อมูลพนักงาน
      const fetchEmployees = async () => {
        try {
          const res = await fetch("http://localhost/MY-VUE-APP/php_api/api_employee.php");
          const data = await res.json();
          employees.value = data.success ? data.data : [];
        } catch (err) {
          error.value = "ไม่สามารถโหลดข้อมูลได้: " + err.message;
        } finally {
          loading.value = false;
        }
      };
  
      const openAddModal = () => {
        isEditMode.value = false;
        editForm.value = { emp_id: "", full_name: "", department: "", salary: "", active: 1, image: "" };
        newImageFile.value = null;
        showModal();
      };
  
      const openEditModal = (emp) => {
        isEditMode.value = true;
        editForm.value = { ...emp };
        newImageFile.value = null;
        showModal();
      };
  
      const showModal = () => {
        const modalEl = document.getElementById("employeeModal");
        modalInstance = new window.bootstrap.Modal(modalEl);
        modalInstance.show();
        // Reset input file
        const fileInput = modalEl.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = "";
      };
  
      const handleFileUpload = (event) => {
        newImageFile.value = event.target.files[0];
      };
  
      const saveEmployee = async () => {
        const formData = new FormData();
        formData.append("action", isEditMode.value ? "update" : "add");
        formData.append("emp_id", editForm.value.emp_id);
        formData.append("full_name", editForm.value.full_name);
        formData.append("department", editForm.value.department);
        formData.append("salary", editForm.value.salary);
        formData.append("active", editForm.value.active);
        if (newImageFile.value) formData.append("image", newImageFile.value);
  
        try {
          const res = await fetch("http://localhost/MY-VUE-APP/php_api/api_employee.php", {
            method: "POST",
            body: formData
          });
          const result = await res.json();
          if (result.message) {
            alert(result.message);
            fetchEmployees();
            modalInstance.hide();
          } else {
            alert(result.error || "เกิดข้อผิดพลาด");
          }
        } catch (err) {
          alert("เชื่อมต่อ Server ล้มเหลว: " + err.message);
        }
      };
  
      const deleteEmployee = async (id) => {
        if (!confirm(`คุณแน่ใจหรือไม่ที่จะลบพนักงานรหัส ${id}?`)) return;
  
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("emp_id", id);
  
        try {
          const res = await fetch("http://localhost/MY-VUE-APP/php_api/api_employee.php", {
            method: "POST",
            body: formData
          });
          const result = await res.json();
          if (result.message) {
            alert(result.message);
            employees.value = employees.value.filter((e) => e.emp_id !== id);
          } else {
            alert(result.error);
          }
        } catch (err) {
          alert(err.message);
        }
      };
  
      onMounted(fetchEmployees);
  
      return {
        employees,
        loading,
        error,
        editForm,
        isEditMode,
        openAddModal,
        openEditModal,
        handleFileUpload,
        saveEmployee,
        deleteEmployee
      };
    }
  };
  </script>