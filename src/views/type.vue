<template>
  <div class="container mt-4">
    <h2 class="mb-3">ประเภทสินค้า</h2>
    
    <div class="mb-3">
      <button class="btn btn-primary" @click="openAddModal">
        Add <i class="bi bi-plus-circle"></i>
      </button>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>รหัสประเภท</th>
          <th>ชื่อประเภท</th>
          <th>แก้ไข/ลบ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="type in types" :key="type.type_id">
          <td>{{ type.type_id }}</td>
          <td>{{ type.type_name }}</td>
          <td>
            <button class="btn btn-warning btn-sm" @click="openEditModal(type)">
              แก้ไข
            </button>
            |
            <button class="btn btn-danger btn-sm" @click="deletetype(type.type_id)">
              ลบ
            </button> 
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div class="modal fade" id="typeModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditMode ? "แก้ไขประเภทสินค้า" : "เพิ่มประเภทสินค้าใหม่" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveType">
              <div class="mb-3">
                <label class="form-label">ชื่อประเภทสินค้า</label>
                <input v-model="editType.type_name" type="text" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-success">
                {{ isEditMode ? "บันทึกการแก้ไข" : "เพิ่มข้อมูล" }}
              </button>
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
  name: "typeList",
  setup() {
    const types = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ✅ เพิ่มตัวแปรจัดการ Modal และสถานะการแก้ไข
    const editType = ref({ type_name: "" });
    const isEditMode = ref(false);
    let typeModal = null;

    const fetchtypes = async () => {
      try {
        const response = await fetch("http://127.168.72.1/MY-VUE-APP/php_api/show_type.php");
        if (!response.ok) throw new Error("ไม่สามารถดึงข้อมูลได้");
        types.value = await response.json();
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchtypes();
      // ✅ ผูกตัวแปรกับ Modal (ต้องมี id="typeModal" ใน template)
      const modalEl = document.getElementById("typeModal");
      if (modalEl) {
        typeModal = new window.bootstrap.Modal(modalEl);
      }
    });

    // ✅ ฟังก์ชันเปิด Modal เพิ่มข้อมูล
    const openAddModal = () => {
      isEditMode.value = false;
      editType.value = { type_name: "" };
      typeModal.show(); // แก้ Error 'show' of null โดยการมี HTML Modal รองรับ
    };

    // ✅ ฟังก์ชันเปิด Modal แก้ไข (แก้ Error: openEditModal is not a function)
    const openEditModal = (type) => {
      isEditMode.value = true;
      editType.value = { ...type }; // Copy ข้อมูล
      typeModal.show();
    };

    // ✅ ฟังก์ชันบันทึกข้อมูล
    const saveType = async () => {
      const url = "http://127.168.72.1/MY-VUE-APP/php_api/type_crud.php";
      const method = isEditMode.value ? "PUT" : "POST";

      try {
        const response = await fetch(url, {
          method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(editType.value)
        });
        const result = await response.json();
        if (result.success) {
          alert(result.message);
          fetchtypes(); // โหลดข้อมูลใหม่
          typeModal.hide();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    // ✅ ฟังก์ชันลบข้อมูล (แก้ Error: deletetype is not a function)
    const deletetype = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่?")) return;
      try {
        const response = await fetch("http://127.168.72.1/MY-VUE-APP/php_api/type_crud.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ type_id: id })
        });
        const result = await response.json();
        if (result.success) {
          types.value = types.value.filter(t => t.type_id !== id);
          alert(result.message);
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    return {
      types, loading, error,
      editType, isEditMode,
      openAddModal, openEditModal, saveType, deletetype // ✅ ต้องส่งค่าออกไปให้ Template ใช้งาน
    };
  }
};
</script>