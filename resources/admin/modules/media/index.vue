<template>
    <div>
        <div class="page-header">Media</div>
        
        <div class="page-content">

            <el-button type="primary" @click="openMedia">
                Open Media Uploader
            </el-button>

            <el-upload
                style="margin-top:50px;"
                drag
                :action="$url('upload')"
                :headers="uploadHeaders"
            >
                <el-icon class="el-icon--upload"><upload-filled /></el-icon>
                
                <div class="el-upload__text">
                    Drop file here or <em>click to upload</em>
                </div>
                <template #tip>
                    <div class="el-upload__tip">
                        jpg/png files with a size less than 500kb
                    </div>
                </template>
            </el-upload>
        </div>
    </div>
</template>

<script setup>
    import config from '@/bootstrap/config';
    import { UploadFilled } from '@element-plus/icons-vue';

    const uploadHeaders = {
        'X-WP-Nonce': config.rest.nonce
    };

    function openMedia() {
        const frame = wp.media({
            title: 'Upload Files',
            button: {
                text: 'Upload',
            },
        });

        frame.open();
    }
</script>
